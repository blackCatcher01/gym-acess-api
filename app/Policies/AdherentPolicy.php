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

        // Un staff ne voit que les adhérents ayant (eu) un abonnement
        // dans SA salle — l'adhérent n'appartenant plus à une salle fixe,
        // on vérifie via ses abonnements plutôt qu'un id_salle direct.
        return $this->aUnAbonnementDansLaSalle($adherent, $utilisateur->staff?->id_salle);
    }

    public function update(Utilisateur $utilisateur, Adherent $adherent): bool
    {
        return $utilisateur->hasAnyRole(['gerant', 'super_admin'])
            && $this->aUnAbonnementDansLaSalle($adherent, $utilisateur->staff?->id_salle);
    }

    private function aUnAbonnementDansLaSalle(Adherent $adherent, ?int $idSalle): bool
    {
        if (! $idSalle) {
            return false;
        }

        return $adherent->abonnements()
            ->whereHas('formule', fn ($q) => $q->where('id_salle', $idSalle))
            ->exists();
    }
}
