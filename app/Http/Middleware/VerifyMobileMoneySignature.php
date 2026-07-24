<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMobileMoneySignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $operateur = $request->route('operateur'); // wave | orange_money | free_money
        $secret = config("services.$operateur.webhook_secret");

        if (! $secret) {
            abort(500, 'Secret webhook non configure pour cet operateur.');
        }

        $signatureRecue = $request->header('X-Signature', '');
        $signatureCalculee = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($signatureCalculee, $signatureRecue)) {
            abort(401, 'Signature invalide.');
        }

        return $next($request);
    }
}