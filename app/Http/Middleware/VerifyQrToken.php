<?php

namespace App\Http\Middleware;

use App\Services\QrTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyQrToken
{
    public function __construct(private readonly QrTokenService $qrTokenService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->input('qr_token');

        if (! $token) {
            return response()->json(['message' => 'qr_token manquant.'], 422);
        }

        $abonnement = $this->qrTokenService->valider($token);

        if (! $abonnement) {
            return response()->json(['message' => 'QR code invalide, expiré ou déjà utilisé.'], 401);
        }

        // Rendu disponible au contrôleur sans re-décoder le token.
        $request->attributes->set('abonnement_scanne', $abonnement);

        return $next($request);
    }
}
