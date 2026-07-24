<?php

namespace App\Services\OtpDelivery;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class InfobipOtpSender
{
    /**
     * @throws RequestException
     */
    public function envoyer(string $telephone, string $code, string $canal): void
    {
        $numero = ltrim($telephone, '+'); // Infobip attend le numero sans "+"

        if ($canal === 'whatsapp') {
            $this->envoyerWhatsapp($numero, $code);

            return;
        }

        $this->envoyerSms($numero, $code);
    }

    private function envoyerSms(string $numero, string $code): void
    {
        $reponse = Http::withHeaders($this->headers())
            ->post($this->url('/sms/3/messages'), [
                'messages' => [[
                    'destinations' => [['to' => $numero]],
                    'sender' => config('services.infobip.sms_sender'),
                    'content' => [
                        'text' => "Votre code de verification est : {$code}. Il expire dans 5 minutes.",
                    ],
                ]],
            ]);

        $reponse->throw();
    }

    private function envoyerWhatsapp(string $numero, string $code): void
    {
        $reponse = Http::withHeaders($this->headers())
            ->post($this->url('/whatsapp/1/message/template'), [
                'messages' => [[
                    'from' => config('services.infobip.whatsapp_sender'),
                    'to' => $numero,
                    'messageId' => (string) \Illuminate\Support\Str::uuid(),
                    'content' => [
                        'templateName' => config('services.infobip.whatsapp_otp_template'),
                        'templateData' => [
                            'body' => ['placeholders' => [$code]],
                            'buttons' => [
                                ['type' => 'COPY_CODE', 'parameter' => $code],
                            ],
                        ],
                        'language' => 'fr',
                    ],
                ]],
            ]);

        $reponse->throw();
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'App ' . config('services.infobip.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function url(string $path): string
    {
        return rtrim(config('services.infobip.base_url'), '/') . $path;
    }
}