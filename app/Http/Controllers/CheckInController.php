<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Passage;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    /**
     * Contrôle d'accès (scénario 3 du cahier des charges).
     * L'abonnement scanné (via son QR) est déjà validé et attaché à la
     * requête par le middleware verify.qr — il porte lui-même la salle
     * concernée (via sa formule), donc pas besoin que le client précise
     * id_salle : ça évite qu'un adhérent scanne le QR d'une salle A pour
     * badger dans une salle B.
     */
    public function scan(Request $request)
    {
        /** @var Abonnement $abonnement */
        $abonnement = $request->attributes->get('abonnement_scanne');
        $adherent = $abonnement->adherent;
        $salle = $abonnement->salle();
        $idCours = $request->input('id_cours');

        [$resultat, $motif] = $this->evaluerAcces($abonnement, $idCours);

        // resultat/motif_refus sont hors $fillable (fixés uniquement ici,
        // jamais depuis le payload client — section 6.7) : on passe par
        // forceFill sur un modèle neuf pour ne faire qu'un seul insert.
        $passage = (new Passage())->forceFill([
            'id_adherent' => $adherent->id_adherent,
            'id_salle' => $salle?->id_salle,
            'id_cours' => $idCours,
            'date_heure_passage' => now(),
            'resultat' => $resultat,
            'motif_refus' => $motif,
        ]);
        $passage->save();

        return response()->json([
            'resultat' => $resultat,
            'motif_refus' => $motif,
            'adherent' => $adherent->utilisateur?->nomComplet(),
            'salle' => $salle?->nom_salle,
        ], $resultat === 'autorise' ? 200 : 403);
    }

    private function evaluerAcces(Abonnement $abonnement, ?int $idCours): array
    {
        if (! $abonnement->estActif()) {
            return ['refuse', 'abonnement_expire'];
        }

        if ($idCours) {
            $reservationConfirmee = $abonnement->adherent->reservations()
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
