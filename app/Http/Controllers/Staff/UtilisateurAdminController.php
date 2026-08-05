<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Utilisateur\CreerAdherentRequest;
use App\Http\Requests\Utilisateur\CreerStaffRequest;
use App\Models\Adherent;
use App\Models\Staff;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Creation d'un compte staff (coach ou gerant) directement par
     * l'administration — pas de flux OTP ici, le compte est actif
     * immediatement avec profil_complete=true (les infos sont deja
     * saisies par l'admin, pas besoin d'onboarding mobile).
     *
     * Regle metier : un gerant ne peut creer qu'un coach, rattache
     * automatiquement a sa propre salle. Seul le super_admin peut
     * creer un gerant, et doit alors preciser id_salle.
     */
    public function creerStaff(CreerStaffRequest $request)
    {
        $donnees = $request->validated();
        $connecte = $request->user();

        if ($donnees['role_staff'] === 'gerant' && ! $connecte->hasRole('super_admin')) {
            abort(403, "Seul l'administrateur peut creer un compte gerant.");
        }

        if ($connecte->hasRole('super_admin')) {
            abort_unless(isset($donnees['id_salle']), 422, 'id_salle est requis.');
            $idSalle = $donnees['id_salle'];
        } else {
            abort_unless($connecte->staff, 403, 'Compte gerant non rattache a une salle.');
            $idSalle = $connecte->staff->id_salle;
        }

        $utilisateur = DB::transaction(function () use ($donnees, $idSalle) {
            $utilisateur = Utilisateur::create([
                'nom' => $donnees['nom'],
                'prenom' => $donnees['prenom'],
                'telephone' => $donnees['telephone'],
                'type_utilisateur' => 'staff',
                'is_active' => true,
            ]);
            // profil_complete est hors $fillable (voir Utilisateur.php) :
            // ici la creation vient de l'admin (donnees deja completes),
            // pas d'un nouveau compte OTP qui doit encore faire l'onboarding.
            $utilisateur->forceFill(['profil_complete' => true])->save();
            $utilisateur->assignRole($donnees['role_staff']);

            Staff::creerPourUtilisateur($utilisateur, [
                'id_salle' => $idSalle,
                'role_staff' => $donnees['role_staff'],
                'date_embauche' => $donnees['date_embauche'] ?? now(),
            ]);

            return $utilisateur;
        });

        return response()->json($utilisateur->load('staff'), 201);
    }

    /**
     * Creation d'un compte adherent "sur place" par le staff — utile
     * pour un adherent qui s'inscrit physiquement sans passer par
     * l'app mobile. Le profil est marque complet directement (l'admin
     * a deja saisi toutes les infos requises par l'onboarding).
     */
    public function creerAdherent(CreerAdherentRequest $request)
    {
        $donnees = $request->validated();

        $adherent = DB::transaction(function () use ($donnees) {
            $utilisateur = Utilisateur::create([
                'nom' => $donnees['nom'],
                'prenom' => $donnees['prenom'],
                'telephone' => $donnees['telephone'],
                'email' => $donnees['email'] ?? null,
                'date_naissance' => $donnees['date_naissance'],
                'sexe' => $donnees['sexe'],
                'type_utilisateur' => 'adherent',
                'is_active' => true,
            ]);
            $utilisateur->forceFill(['profil_complete' => true])->save();

            return Adherent::creerPourUtilisateur($utilisateur);
        });

        return response()->json($adherent->load('utilisateur'), 201);
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