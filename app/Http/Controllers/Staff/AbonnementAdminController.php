<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salle\CreerAbonnementAdminRequest;
use App\Models\Adherent;
use App\Models\FormuleAbonnement;
use App\Models\JournalAudit;
use App\Models\Paiement;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbonnementAdminController extends Controller
{
    /**
     * Recherche d'un utilisateur par numero de telephone, pour un
     * adherent physiquement present a l'accueil (scenario "creer un
     * abonnement en le recherchant par son numero"). L'adherent doit
     * deja avoir un compte (meme incomplet) — voir le message d'erreur
     * si non trouve.
     */
    public function rechercherAdherent(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['coach', 'gerant', 'super_admin']), 403);

        $telephone = $request->string('telephone')->toString();
        abort_if(! $telephone, 422, 'Numero de telephone requis.');

        $utilisateur = Utilisateur::where('telephone', 'like', "%{$telephone}%")
            ->where('type_utilisateur', 'adherent')
            ->with('adherent.abonnements.formule.salle')
            ->first();

        if (! $utilisateur) {
            return response()->json([
                'message' => "Aucun compte trouve pour ce numero. L'adherent doit d'abord creer son compte via l'application (numero + code recu par SMS).",
            ], 404);
        }

        return response()->json($utilisateur);
    }

    /**
     * Cree l'abonnement ET confirme immediatement le paiement — contexte
     * different de SouscriptionController::souscrire() (a distance) :
     * ici le staff valide physiquement la transaction (especes en main,
     * ou preuve de paiement mobile money deja recue), donc le paiement
     * est enregistre "confirme" directement plutot que "en_attente".
     */
    public function creer(CreerAbonnementAdminRequest $request)
    {
        $donnees = $request->validated();
        $staff = $request->user()->staff;

        abort_unless($staff, 403, 'Reserve au personnel rattache a une salle.');

        $formule = FormuleAbonnement::findOrFail($donnees['id_formule']);
        abort_unless($formule->id_salle === $staff->id_salle, 403, "Cette formule n'appartient pas a votre salle.");

        $utilisateur = Utilisateur::where('telephone', $donnees['telephone'])->first();
        abort_unless($utilisateur, 404, "Aucun compte trouve pour ce numero.");

        $adherent = $utilisateur->adherent ?? Adherent::creerPourUtilisateur($utilisateur);

        [$abonnement, $paiement] = DB::transaction(function () use ($adherent, $formule, $donnees, $staff) {
            $abonnement = $adherent->abonnements()->create([
                'id_formule' => $formule->id_formule,
                'date_debut' => now(),
                'date_fin' => now()->addDays($formule->duree_jours),
                'statut' => 'actif', // valide immediatement par le staff
            ]);

            $paiement = new Paiement([
                'id_abonnement' => $abonnement->id_abonnement,
                'montant' => $formule->prix,
                'moyen_paiement' => $donnees['moyen_paiement'],
                'reference_transaction' => 'ADMIN-' . $abonnement->id_abonnement . '-' . Str::upper(Str::random(8)),
                'date_paiement' => now(),
            ]);
            // statut_paiement est hors $fillable (voir Paiement.php) —
            // forceFill est le seul chemin legitime pour le fixer ici
            // (le staff valide physiquement la transaction).
            $paiement->forceFill(['statut_paiement' => 'confirme'])->save();

            JournalAudit::create([
                'id_staff' => $staff->id_staff,
                'id_salle' => $staff->id_salle,
                'action' => 'Creation abonnement manuel (formule: ' . $formule->nom_formule . ')',
                'date_action' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => "Adherent id={$adherent->id_adherent}, montant={$formule->prix}",
            ]);

            return [$abonnement, $paiement];
        });

        return response()->json([
            'abonnement' => $abonnement->load('formule.salle'),
            'paiement' => $paiement,
        ], 201);
    }
}
