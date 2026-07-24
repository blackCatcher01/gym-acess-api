<?php

namespace App\Jobs;

use App\Models\AuthOtp;
use App\Services\OtpDelivery\InfobipOtpSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10; // secondes entre chaque tentative

    public function __construct(
        private readonly int $idOtp,
        private readonly string $telephone,
        private readonly string $code,
        private readonly string $canal,
    ) {}

    public function handle(InfobipOtpSender $sender): void
    {
        $sender->envoyer($this->telephone, $this->code, $this->canal);
    }

    /**
     * Si les 3 tentatives échouent, on marque l'OTP comme non délivrable
     * plutôt que de laisser un enregistrement "emitted" fantôme que
     * l'utilisateur ne pourra jamais saisir.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Echec envoi OTP apres plusieurs tentatives', [
            'id_otp' => $this->idOtp,
            'telephone' => $this->telephone,
            'erreur' => $exception->getMessage(),
        ]);

        AuthOtp::where('id_otp', $this->idOtp)->update(['statut' => 'failed']);
    }
}
