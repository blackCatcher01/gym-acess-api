<?php

namespace App\Services;

use App\Models\Adherent;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class QrTokenService
{
    private const ALGO = 'HS256';
    private const DUREE_VALIDITE_MIN = 10;

    public function generer(Adherent $adherent): string
    {
        $payload = [
            'sub' => $adherent->id_adherent,
            'nonce' => Str::uuid()->toString(),
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(self::DUREE_VALIDITE_MIN)->timestamp,
        ];

        $token = JWT::encode($payload, config('app.key'), self::ALGO);

        $adherent->update(['qr_token' => $token]);

        return $token;
    }

    /**
     * Décode et valide le token. Ne retourne l'adhérent QUE si :
     * - la signature est valide et le token n'est pas expiré,
     * - le token correspond à celui actuellement enregistré en base
     *   (permet une révocation immédiate en régénérant le QR),
     * - le nonce n'a pas déjà été utilisé récemment (anti-rejeu).
     */
    public function valider(string $token): ?Adherent
    {
        try {
            $payload = JWT::decode($token, new Key(config('app.key'), self::ALGO));
        } catch (\Throwable) {
            return null;
        }

        $adherent = Adherent::find($payload->sub);

        if (! $adherent || $adherent->qr_token !== $token) {
            return null; // token révoqué / regénéré depuis
        }

        $cleAntiRejeu = 'qr-nonce:' . $payload->nonce;

        if (cache()->has($cleAntiRejeu)) {
            return null; // déjà scanné très récemment → partage de compte suspecté
        }

        cache()->put($cleAntiRejeu, true, now()->addSeconds(self::DUREE_VALIDITE_MIN * 60));

        return $adherent;
    }
}