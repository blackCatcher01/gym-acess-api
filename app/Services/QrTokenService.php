<?php

namespace App\Services;

use App\Models\Abonnement;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

/**
 * Le QR est désormais propre à chaque ABONNEMENT (et non plus à
 * l'adhérent) : un utilisateur peut être abonné à plusieurs salles en
 * simultané, chacune avec son propre QR d'accès — cohérent avec le fait
 * qu'un adhérent n'est plus rattaché à une seule salle fixe.
 */
class QrTokenService
{
    private const ALGO = 'HS256';
    private const DUREE_VALIDITE_MIN = 10;

    public function generer(Abonnement $abonnement): string
    {
        $payload = [
            'sub' => $abonnement->id_abonnement,
            'nonce' => Str::uuid()->toString(),
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(self::DUREE_VALIDITE_MIN)->timestamp,
        ];

        $token = JWT::encode($payload, config('app.key'), self::ALGO);

        // qr_token est hors $fillable (voir Abonnement.php) : forceFill
        // est le seul chemin légitime pour l'écrire.
        $abonnement->forceFill(['qr_token' => $token])->save();

        return $token;
    }

    /**
     * Décode et valide le token. Ne retourne l'abonnement QUE si :
     * - la signature est valide et le token n'est pas expiré,
     * - le token correspond à celui actuellement enregistré en base
     *   (permet une révocation immédiate en régénérant le QR),
     * - le nonce n'a pas déjà été utilisé récemment (anti-rejeu).
     */
    public function valider(string $token): ?Abonnement
    {
        try {
            $payload = JWT::decode($token, new Key(config('app.key'), self::ALGO));
        } catch (\Throwable) {
            return null;
        }

        $abonnement = Abonnement::with(['adherent.utilisateur', 'formule.salle'])->find($payload->sub);

        if (! $abonnement || $abonnement->qr_token !== $token) {
            return null; // token révoqué / regénéré depuis
        }

        $cleAntiRejeu = 'qr-nonce:' . $payload->nonce;

        if (cache()->has($cleAntiRejeu)) {
            return null; // déjà scanné très récemment → partage de compte suspecté
        }

        cache()->put($cleAntiRejeu, true, now()->addSeconds(self::DUREE_VALIDITE_MIN * 60));

        return $abonnement;
    }
}
