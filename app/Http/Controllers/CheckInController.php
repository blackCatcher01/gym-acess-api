<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Passage;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    /**
     * Contrôle d'accès (scénario 3 du cahier des charges).
     * L'adhérent est déjà validé et attaché à la requête par le
     * middleware verify.qr — on applique ici uniquement les règles
     * métier (abonnement actif, réservation le cas échéant).
     */
    public function scan(Request $request)
    {
        /** @var Adherent $adherent */
        $adherent = $request->attributes->get('adherent_scanne');

        $idSalle = $request->integer('id_salle') ?: $adherent->id_salle;
        $idCours = $request->input('id_cours');

        [$resultat, $motif] = $this->evaluerAcces($adherent, $idCours);

        // resultat/motif_refus sont hors $fillable (fixés uniquement ici,
        // jamais depuis le payload client — section 6.7) : on passe par
        // forceFill sur un modèle neuf pour ne faire qu'un seul insert.
        $passage = (new Passage())->forceFill([
            'id_adherent' => $adherent->id_adherent,
            'id_salle' => $idSalle,
            'id_cours' => $idCours,
            'date_heure_passage' => now(),
            'resultat' => $resultat,
            'motif_refus' => $motif,
        ]);
        $passage->save();

        return response()->json([
            'resultat' => $resultat,
            'motif_refus' => $motif,
            'adherent' => $adherent->utilisateur?->nom,
        ], $resultat === 'autorise' ? 200 : 403);
    }

    private function evaluerAcces(Adherent $adherent, ?int $idCours): array
    {
        $abonnementActif = $adherent->abonnements()
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now()->toDateString())
            ->exists();

        if (! $abonnementActif) {
            return ['refuse', 'abonnement_expire'];
        }

        if ($idCours) {
            $reservationConfirmee = $adherent->reservations()
                ->where('id_cours', $idCours)
                ->where('statut_reservation', 'confirmee')
                ->exists();

            if (! $reservationConfirmee) {
                return ['refuse', 'pas_de_reservation'];
            }
        }

        return ['autorise', null];
    }
}
