<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class UtilisateurAdminController extends Controller
{
    public function adherents(Request $request)
    {
        $this->autoriserStaff($request);
        $staff = $request->user()->staff;

        $adherents = Utilisateur::query()
            ->where('type_utilisateur', 'adherent')
            ->when(! $request->user()->hasRole('super_admin'), function ($q) use ($staff) {
                $q->whereHas('adherent.abonnements.formule', fn ($qq) => $qq->where('id_salle', $staff->id_salle));
            })
            ->with(['adherent.abonnements' => fn ($q) => $q->latest('date_fin')->limit(1)])
            ->orderBy('nom')
            ->paginate($request->integer('par_page', 20));

        return response()->json($adherents);
    }

    public function staff(Request $request)
    {
        $this->autoriserStaff($request);
        $utilisateur = $request->user();
        $staff = $utilisateur->staff;

        $membres = Utilisateur::query()
            ->where('type_utilisateur', 'staff')
            ->when(! $utilisateur->hasRole('super_admin'), function ($q) use ($staff) {
                $q->whereHas('staff', fn ($qq) => $qq->where('id_salle', $staff->id_salle));
            })
            ->with('staff')
            ->orderBy('nom')
            ->paginate($request->integer('par_page', 20));

        return response()->json($membres);
    }

    /**
     * Active/désactive un compte — is_active est explicitement dans
     * $fillable sur Utilisateur, donc update() classique suffit ici.
     */
    public function basculerStatut(Request $request, Utilisateur $utilisateur)
    {
        abort_unless($request->user()->hasAnyRole(['gerant', 'super_admin']), 403);

        $donnees = $request->validate(['is_active' => ['required', 'boolean']]);
        $utilisateur->update($donnees);

        return response()->json($utilisateur);
    }

    private function autoriserStaff(Request $request): void
    {
        abort_unless($request->user()->hasAnyRole(['coach', 'gerant', 'super_admin']), 403);
    }
}