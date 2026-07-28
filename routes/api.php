<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\BanniereController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\Marketplace\BoutiqueController;
use App\Http\Controllers\Marketplace\CategorieProduitController;
use App\Http\Controllers\Marketplace\ProduitController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\SouscriptionController;
use App\Http\Controllers\Staff\AbonnementAdminController;
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
    // -- Profil & onboarding --
    Route::get('/mes-qr', [QrController::class, 'mesCodes']);
    Route::patch('/mon-profil', [ProfilController::class, 'completer']);
    Route::get('/centres-interet', [ProfilController::class, 'centresInteretDisponibles']);

    // -- Accueil --
    Route::get('/bannieres', [BanniereController::class, 'index']);
    Route::post('/bannieres', [BanniereController::class, 'store']);
    Route::patch('/bannieres/{banniere}', [BanniereController::class, 'update']);
    Route::delete('/bannieres/{banniere}', [BanniereController::class, 'destroy']);

    // -- Marketplace --
    Route::get('/marketplace/boutiques', [BoutiqueController::class, 'index']);
    Route::get('/marketplace/boutiques/{boutique}', [BoutiqueController::class, 'show']);
    Route::post('/marketplace/boutiques', [BoutiqueController::class, 'store']);
    Route::patch('/marketplace/boutiques/{boutique}', [BoutiqueController::class, 'update']);
    Route::delete('/marketplace/boutiques/{boutique}', [BoutiqueController::class, 'destroy']);

    Route::get('/marketplace/categories', [CategorieProduitController::class, 'index']);
    Route::post('/marketplace/categories', [CategorieProduitController::class, 'store']);
    Route::delete('/marketplace/categories/{categorie}', [CategorieProduitController::class, 'destroy']);

    Route::get('/marketplace/produits', [ProduitController::class, 'index']);
    Route::get('/marketplace/produits/{produit}', [ProduitController::class, 'show']);
    Route::post('/marketplace/produits', [ProduitController::class, 'store']);
    Route::patch('/marketplace/produits/{produit}', [ProduitController::class, 'update']);
    Route::delete('/marketplace/produits/{produit}', [ProduitController::class, 'destroy']);

    // -- Salles & souscription --
    Route::get('/salles', [SalleController::class, 'index']);
    Route::get('/salles/{salle}', [SalleController::class, 'show']);
    Route::post('/salles/{salle}/formules/{formule}/souscrire', [SouscriptionController::class, 'souscrire']);

    // -- Creation d'abonnement par le staff (adherent present physiquement) --
    Route::get('/staff/adherents/rechercher', [AbonnementAdminController::class, 'rechercherAdherent']);
    Route::post('/staff/abonnements', [AbonnementAdminController::class, 'creer']);

    // -- Cours & reservations --
    Route::get('/cours', [CoursController::class, 'index']);
    Route::get('/cours/{cours}', [CoursController::class, 'show']);
    Route::post('/cours', [CoursController::class, 'store']);
    Route::patch('/cours/{cours}', [CoursController::class, 'update']);
    Route::delete('/cours/{cours}', [CoursController::class, 'destroy']);

    Route::get('/mes-reservations', [ReservationController::class, 'mesReservations']);
    Route::post('/reservations', [ReservationController::class, 'reserver']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'annuler']);
});

Route::middleware('verify.mobile-money')
    ->post('/webhooks/{operateur}/paiement', [MobileMoneyWebhookController::class, 'handle']);
