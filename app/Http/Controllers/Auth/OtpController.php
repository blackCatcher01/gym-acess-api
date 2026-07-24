<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
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

        AuthOtp::create([
            'id_utilisateur' => Utilisateur::where('telephone', $data['telephone'])->value('id_utilisateur'),
            'telephone' => $data['telephone'],
            'otp_hash' => $this->otpService->hash($code = $this->otpService->generateCode()),
            'purpose' => $data['purpose'] ?? 'login',
            'statut' => 'emitted',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Envoi réel via WhatsApp/SMS — service à brancher (Twilio, Infobip, etc.)
        // Ne jamais logger $code en clair en production.
        // dispatch(new SendOtpNotification($data['telephone'], $code));

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

        $valide = $otp && $this->otpService->verify($data['code'], $otp->otp_hash);

        LoginAttempt::create([
            'telephone' => $data['telephone'],
            'ip_address' => $request->ip(),
            'succes' => $valide,
        ]);

        if (! $valide) {
            return response()->json(['message' => 'Code invalide ou expiré.'], 422);
        }

        $otp->update(['statut' => 'consumed']);

        $utilisateur = Utilisateur::firstOrCreate(
            ['telephone' => $data['telephone']],
            ['nom' => $data['nom'] ?? 'Nouvel utilisateur', 'type_utilisateur' => 'adherent', 'is_active' => true]
        );

        $utilisateur->update(['last_login_at' => now()]);

        $token = $utilisateur->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'utilisateur' => $utilisateur,
        ]);
    }

    public function resendOtp(RequestOtpRequest $request)
    {
        return $this->requestOtp($request);
    }
}