<?php

namespace App\Http\Controllers;

use App\Services\QrTokenService;
use Illuminate\Http\Request;

class QrController extends Controller
{
    public function __construct(private readonly QrTokenService $qrTokenService) {}

    /**
     * Renvoie un QR par abonnement actif de l'adhérent connecté — un
     * utilisateur peut être abonné à plusieurs salles en simultané,
     * chacune avec sa propre carte QR côté app mobile.
     */
    public function mesCodes(Request $request)
    {
        $adherent = $request->user()->adherent;

        if (! $adherent) {
            return response()->json(['message' => 'Aucun profil adhérent associé à ce compte.'], 404);
        }

        $abonnements = $adherent->abonnements()
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now()->toDateString())
            ->with('formule.salle')
            ->get();

        $codes = $abonnements->map(function ($abonnement) {
            return [
                'id_abonnement' => $abonnement->id_abonnement,
                'nom_salle' => $abonnement->salle()?->nom_salle,
                'formule' => $abonnement->formule->nom_formule,
                'date_fin' => $abonnement->date_fin,
                'qr_token' => $this->qrTokenService->generer($abonnement),
            ];
        });

        return response()->json($codes);
    }
}
