<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profil\CompleterProfilRequest;
use App\Models\Adherent;
use App\Models\CentreInteret;
use Illuminate\Support\Facades\DB;

class ProfilController extends Controller
{
    /**
     * Onboarding : appelé juste après la première vérification OTP d'un
     * nouveau compte. Complète le profil, crée le profil Adherent associé
     * (plus besoin de choisir une salle à ce stade — voir Adherent::
     * creerPourUtilisateur) et marque le profil comme complet.
     */
    public function completer(CompleterProfilRequest $request)
    {
        $utilisateur = $request->user();
        $donnees = $request->validated();
        $centresInteret = $donnees['centres_interet'] ?? [];
        unset($donnees['centres_interet']);

        DB::transaction(function () use ($utilisateur, $donnees, $centresInteret) {
            $utilisateur->update($donnees);

            // profil_complete est hors $fillable (voir Utilisateur.php) :
            // c'est cette action précise qui a le droit de le passer à true.
            $utilisateur->forceFill(['profil_complete' => true])->save();

            if ($utilisateur->type_utilisateur === 'adherent' && ! $utilisateur->adherent) {
                Adherent::creerPourUtilisateur($utilisateur);
            }

            if (! empty($centresInteret)) {
                $utilisateur->centresInteret()->sync($centresInteret);
            }
        });

        return response()->json([
            'utilisateur' => $utilisateur->fresh(['centresInteret', 'adherent']),
        ]);
    }

    /**
     * Liste des centres d'intérêt disponibles, pour peupler le picker
     * pendant l'onboarding.
     */
    public function centresInteretDisponibles()
    {
        return response()->json(CentreInteret::orderBy('nom')->get());
    }
}
