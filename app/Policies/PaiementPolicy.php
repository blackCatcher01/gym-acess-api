<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\Utilisateur;

class PaiementPolicy
{
    public function view(Utilisateur $utilisateur, Paiement $paiement): bool
    {
        $idSalleAdherent = $paiement->abonnement->adherent->id_salle;

        if ($utilisateur->type_utilisateur === 'adherent') {
            return $utilisateur->id_utilisateur === $paiement->abonnement->id_adherent;
        }

        // Seuls gérant/super_admin voient les données financières —
        // un coach n'y a pas accès (principe du moindre privilège, section 6.2).
        return $utilisateur->hasAnyRole(['gerant', 'super_admin'])
            && $utilisateur->staff?->id_salle === $idSalleAdherent;
    }

    // Le statut de paiement n'est jamais modifiable via une policy "update"
    // classique : seule la route webhook signée peut le faire (section 6.6).
}