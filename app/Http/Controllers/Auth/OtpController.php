<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Jobs\SendOtpJob;
use App\Models\AuthOtp;
use App\Models\LoginAttempt;
use App\Models\Utilisateur;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    public function requestOtp(RequestOtpRequest $request)
    {
        $data = $request->validated();

        $otp = AuthOtp::create([
            'id_utilisateur' => Utilisateur::where('telephone', $data['telephone'])->value('id_utilisateur'),
            'telephone' => $data['telephone'],
            'otp_hash' => $this->otpService->hash($code = $this->otpService->generateCode()),
            'purpose' => $data['purpose'] ?? 'login',
            'statut' => 'emitted',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Envoi réel via WhatsApp/SMS (Infobip) en file d'attente.
        // Ne jamais logger $code en clair en production.
        SendOtpJob::dispatch(
            $otp->id_otp,
            $data['telephone'],
            $code,
            config('services.otp_channel', 'sms'),
        );

        return response()->json(['message' => 'Code envoyé.']);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $data = $request->validated();

        // Vérification persistante des tentatives récentes (en plus du
        // throttle en mémoire) : protège même après un redémarrage serveur.
        $echecsRecents = LoginAttempt::where('telephone', $data['telephone'])
            ->where('succes', false)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($echecsRecents >= 5) {
            return response()->json(['message' => 'Trop de tentatives. Réessayez plus tard.'], 429);
        }

        $otp = AuthOtp::where('telephone', $data['telephone'])
            ->where('purpose', $data['purpose'] ?? 'login')
            ->where('statut', 'emitted')
            ->where('expires_at', '>=', now())
            ->latest('id_otp')
            ->first();

        // Code de contournement pour les tests manuels / automatisés.
        // IMPOSSIBLE en production : app()->environment('production') doit
        // être false, donc dépend uniquement de APP_ENV dans .env — jamais
        // d'un flag qu'on pourrait oublier de désactiver.
        $estCodeTest = $data['code'] === '000000' && ! app()->environment('production');

        $valide = $estCodeTest || ($otp && $this->otpService->verify($data['code'], $otp->otp_hash));

        LoginAttempt::create([
            'telephone' => $data['telephone'],
            'ip_address' => $request->ip(),
            'succes' => $valide,
        ]);

        if (! $valide) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        // $otp peut être null si on utilise le code de test sans avoir
        // demandé de vrai OTP au préalable.
        $otp?->update(['statut' => 'consumed']);

        $utilisateur = Utilisateur::firstOrCreate(
            ['telephone' => $data['telephone']],
            [
                'nom' => $data['nom'] ?? 'Nouvel utilisateur',
                'prenom' => $data['prenom'] ?? '',
                'type_utilisateur' => 'adherent',
                'is_active' => true,
            ]
        );

        // firstOrCreate(), sur la branche "création", n'hydrate en mémoire
        // que les attributs explicitement fournis (pas un vrai SELECT *) —
        // sans ce refresh(), les colonnes comme profil_complete seraient
        // carrément absentes du JSON (pas juste null) pour un tout nouvel
        // utilisateur, ce qui casserait le parsing côté app mobile.
        $utilisateur->refresh();

        // update() passe par $fillable, qui exclut volontairement last_login_at
        // (c'est un champ que le client ne doit jamais pouvoir forcer) —
        // forceFill() est donc nécessaire ici, c'est le seul endroit légitime
        // où ce champ doit être écrit.
        $utilisateur->forceFill(['last_login_at' => now()])->save();

        $token = $utilisateur->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'utilisateur' => $utilisateur,
            // Necessaire cote admin web pour filtrer les menus reserves
            // (Marketplace, Bannieres) au super_admin.
            'roles' => $utilisateur->getRoleNames(),
        ]);
    }

    public function resendOtp(RequestOtpRequest $request)
    {
        return $this->requestOtp($request);
    }
}