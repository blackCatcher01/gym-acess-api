<?php

namespace App\Policies;

use App\Models\Adherent;
use App\Models\Utilisateur;

class AdherentPolicy
{
    public function view(Utilisateur $utilisateur, Adherent $adherent): bool
    {
        // Un adhérent ne voit que sa propre fiche.
        if ($utilisateur->type_utilisateur === 'adherent') {
            return $utilisateur->id_utilisateur === $adherent->id_adherent;
        }

        // Un staff ne voit que les adhérents de sa salle.
        return $utilisateur->staff?->id_salle === $adherent->id_salle;
    }

    public function update(Utilisateur $utilisateur, Adherent $adherent): bool
    {
        return $utilisateur->hasAnyRole(['gerant', 'super_admin'])
            && $utilisateur->staff?->id_salle === $adherent->id_salle;
    }
}