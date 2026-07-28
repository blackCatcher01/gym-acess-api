<?php

namespace App\Http\Controllers;

use App\Http\Requests\Salle\SouscrireFormuleRequest;
use App\Models\Adherent;
use App\Models\FormuleAbonnement;
use App\Models\Paiement;
use App\Models\Salle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SouscriptionController extends Controller
{
    /**
     * Souscription a distance (scenario "Salle de sport" du cahier des
     * charges) : l'adherent choisit une formule depuis l'app, sans se
     * deplacer. L'abonnement reste en statut "a_renouveler" (= en attente
     * d'activation) tant que le paiement n'est pas confirme :
     * - especes : un membre du staff confirmera a l'accueil (voir
     *   Staff\AbonnementAdminController), qui active l'abonnement ;
     * - mobile money : le webhook operateur (deja construit, voir
     *   MobileMoneyWebhookController) confirmera et activera automatiquement.
     *
     * NOTE : l'appel reel a l'API du fournisseur Mobile Money pour
     * initier le paiement (obtenir un lien de paiement a presenter a
     * l'utilisateur) n'est pas encore branche ici — a completer avec les
     * identifiants Wave/Orange/Free une fois obtenus.
     */
    public function souscrire(SouscrireFormuleRequest $request, Salle $salle, FormuleAbonnement $formule)
    {
        abort_unless($formule->id_salle === $salle->id_salle, 404, 'Cette formule n\'appartient pas a cette salle.');
        abort_unless($formule->actif, 422, 'Cette formule n\'est plus disponible.');

        $utilisateur = $request->user();

        $adherent = $utilisateur->adherent ?? Adherent::creerPourUtilisateur($utilisateur);

        $dejaActif = $adherent->abonnements()
            ->where('id_formule', $formule->id_formule)
            ->where('statut', 'actif')
            ->where('date_fin', '>=', now()->toDateString())
            ->exists();

        abort_if($dejaActif, 422, 'Vous avez deja un abonnement actif sur cette formule.');

        $donnees = $request->validated();

        $resultat = DB::transaction(function () use ($adherent, $formule, $donnees) {
            $abonnement = $adherent->abonnements()->create([
                'id_formule' => $formule->id_formule,
                'date_debut' => now(),
                'date_fin' => now()->addDays($formule->duree_jours),
                'statut' => 'a_renouveler', // active a la confirmation du paiement
            ]);

            $paiement = new Paiement([
                'id_abonnement' => $abonnement->id_abonnement,
                'montant' => $formule->prix,
                'moyen_paiement' => $donnees['moyen_paiement'],
                'reference_transaction' => 'SUB-' . $abonnement->id_abonnement . '-' . Str::upper(Str::random(8)),
                'date_paiement' => now(),
            ]);
            // statut_paiement est hors $fillable (voir Paiement.php) —
            // forceFill est le seul chemin legitime pour le fixer ici.
            $paiement->forceFill(['statut_paiement' => 'en_attente'])->save();

            return [$abonnement, $paiement];
        });

        [$abonnement, $paiement] = $resultat;

        return response()->json([
            'abonnement' => $abonnement->load('formule.salle'),
            'paiement' => $paiement,
            'message' => $donnees['moyen_paiement'] === 'especes'
                ? 'Abonnement cree. Presentez-vous a la salle pour finaliser le paiement en especes.'
                : 'Abonnement cree, en attente de confirmation du paiement.',
        ], 201);
    }
}
