<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\Webhooks\MobileMoneyWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('throttle:login')->group(function () {
    Route::post('/auth/otp/request', [OtpController::class, 'requestOtp']);
    Route::post('/auth/otp/verify', [OtpController::class, 'verifyOtp']);
});

Route::middleware('throttle:otp')->group(function () {
    Route::post('/auth/otp/resend', [OtpController::class, 'resendOtp']);
});

Route::middleware(['auth:sanctum', 'verify.qr'])
    ->post('/checkin', [CheckInController::class, 'scan']);

Route::middleware('auth:sanctum')->get('/mon-qr', function (Request $request) {
    $adherent = $request->user()->adherent;

    if (! $adherent) {
        return response()->json([
            'message' => "Aucun profil adhérent associé à ce compte.",
        ], 404);
    }

    $token = app(\App\Services\QrTokenService::class)->generer($adherent);

    return response()->json(['qr_token' => $token]);
});

Route::middleware('verify.mobile-money')
    ->post('/webhooks/{operateur}/paiement', [MobileMoneyWebhookController::class, 'handle']);
