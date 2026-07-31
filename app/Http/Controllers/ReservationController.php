<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Models\Cours;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function mesReservations(Request $request)
    {
        $adherent = $request->user()->adherent;
        abort_unless($adherent, 404, 'Aucun profil adherent associe a ce compte.');

        $reservations = $adherent->reservations()
            ->with('cours.salle')
            ->orderByDesc('date_reservation')
            ->paginate($request->integer('par_page', 20));

        return response()->json($reservations);
    }

    /**
     * Reservation avec verrouillage pessimiste pour eviter une
     * sur-reservation en cas de deux requetes quasi simultanees sur les
     * toutes dernieres places (condition de course classique).
     */
    public function reserver(StoreReservationRequest $request)
    {
        $adherent = $request->user()->adherent;
        abort_unless($adherent, 404, 'Aucun profil adherent associe a ce compte.');

        $idCours = $request->validated('id_cours');

        return DB::transaction(function () use ($adherent, $idCours) {
            $cours = Cours::lockForUpdate()->findOrFail($idCours);

            $dejaReserve = $cours->reservations()
                ->where('id_adherent', $adherent->id_adherent)
                ->where('statut_reservation', '!=', 'annulee')
                ->exists();
            abort_if($dejaReserve, 422, 'Vous avez deja une reservation pour ce cours.');

            // L'adherent doit avoir un abonnement actif dans la salle du cours.
            $aUnAbonnementActif = $adherent->abonnements()
                ->where('statut', 'actif')
                ->where('date_fin', '>=', now()->toDateString())
                ->whereHas('formule', fn ($q) => $q->where('id_salle', $cours->id_salle))
                ->exists();
            abort_unless($aUnAbonnementActif, 403, "Un abonnement actif dans cette salle est requis pour reserver.");

            $placesConfirmees = $cours->reservations()->where('statut_reservation', 'confirmee')->count();
            $statut = $placesConfirmees < $cours->capacite_max ? 'confirmee' : 'liste_attente';

            $reservation = Reservation::create([
                'id_adherent' => $adherent->id_adherent,
                'id_cours' => $cours->id_cours,
                'statut_reservation' => $statut,
                'date_reservation' => now(),
            ]);

            return response()->json([
                'reservation' => $reservation,
                'message' => $statut === 'confirmee'
                    ? 'Reservation confirmee.'
                    : "Cours complet : vous etes en liste d'attente.",
            ], 201);
        });
    }

    /**
     * Annulation + promotion automatique du premier de la liste d'attente
     * (evite qu'une place liberee reste inutilisee sans intervention manuelle).
     */
    public function annuler(Request $request, Reservation $reservation)
    {
        $adherent = $request->user()->adherent;
        abort_unless($adherent && $reservation->id_adherent === $adherent->id_adherent, 403);
        abort_if($reservation->statut_reservation === 'annulee', 422, 'Deja annulee.');

        DB::transaction(function () use ($reservation) {
            $etaitConfirmee = $reservation->statut_reservation === 'confirmee';
            $reservation->update(['statut_reservation' => 'annulee']);

            if ($etaitConfirmee) {
                $premierEnAttente = Reservation::where('id_cours', $reservation->id_cours)
                    ->where('statut_reservation', 'liste_attente')
                    ->oldest('date_reservation')
                    ->lockForUpdate()
                    ->first();

                $premierEnAttente?->update(['statut_reservation' => 'confirmee']);
            }
        });

        return response()->json(['message' => 'Reservation annulee.']);
    }

    /**
     * Vue admin/staff de toutes les réservations de la salle (distinct
     * de mesReservations, qui ne montre que celles de l'adhérent connecté).
     */
    public function indexAdmin(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['coach', 'gerant', 'super_admin']), 403);
        $staff = $request->user()->staff;

        $reservations = Reservation::query()
            ->with(['adherent.utilisateur', 'cours.salle'])
            ->when(! $request->user()->hasRole('super_admin'), function ($q) use ($staff) {
                $q->whereHas('cours', fn ($qq) => $qq->where('id_salle', $staff->id_salle));
            })
            ->when($request->string('statut')->toString(), fn ($q, $s) => $q->where('statut_reservation', $s))
            ->orderByDesc('date_reservation')
            ->paginate($request->integer('par_page', 20));

        return response()->json($reservations);
    }

    /**
     * Annulation côté staff (distincte de annuler(), réservée à
     * l'adhérent propriétaire) — même logique de promotion automatique
     * de la liste d'attente.
     */
    public function annulerParStaff(Request $request, Reservation $reservation)
    {
        abort_unless($request->user()->hasAnyRole(['coach', 'gerant', 'super_admin']), 403);
        abort_if($reservation->statut_reservation === 'annulee', 422, 'Deja annulee.');

        DB::transaction(function () use ($reservation) {
            $etaitConfirmee = $reservation->statut_reservation === 'confirmee';
            $reservation->update(['statut_reservation' => 'annulee']);

            if ($etaitConfirmee) {
                $premierEnAttente = Reservation::where('id_cours', $reservation->id_cours)
                    ->where('statut_reservation', 'liste_attente')
                    ->oldest('date_reservation')
                    ->lockForUpdate()
                    ->first();

                $premierEnAttente?->update(['statut_reservation' => 'confirmee']);
            }
        });

        return response()->json(['message' => 'Reservation annulee.']);
    }
}
