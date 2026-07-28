<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\QrController;
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

Route::middleware('auth:sanctum')->group(function () {
    // Un QR par abonnement actif (un adherent peut etre abonne a
    // plusieurs salles en simultane).
    Route::get('/mes-qr', [QrController::class, 'mesCodes']);

    // Onboarding : complétion du profil + création du profil Adherent.
    Route::patch('/mon-profil', [ProfilController::class, 'completer']);
    Route::get('/centres-interet', [ProfilController::class, 'centresInteretDisponibles']);
});

Route::middleware('verify.mobile-money')
    ->post('/webhooks/{operateur}/paiement', [MobileMoneyWebhookController::class, 'handle']);
