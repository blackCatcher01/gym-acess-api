<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\Utilisateur;

class PaiementPolicy
{
    public function view(Utilisateur $utilisateur, Paiement $paiement): bool
    {
        if ($utilisateur->type_utilisateur === 'adherent') {
            return $utilisateur->id_utilisateur === $paiement->abonnement->id_adherent;
        }

        // Seuls gérant/super_admin voient les données financières —
        // un coach n'y a pas accès (principe du moindre privilège, section 6.2).
        // La salle concernée est celle de l'abonnement (via sa formule),
        // pas celle de l'adhérent qui n'a plus de salle fixe.
        $idSalleDuPaiement = $paiement->abonnement->salle()?->id_salle;

        return $utilisateur->hasAnyRole(['gerant', 'super_admin'])
            && $idSalleDuPaiement !== null
            && $utilisateur->staff?->id_salle === $idSalleDuPaiement;
    }

    // Le statut de paiement n'est jamais modifiable via une policy "update"
    // classique : seule la route webhook signée peut le faire (section 6.6).
}
