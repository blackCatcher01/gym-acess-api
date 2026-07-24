<?php

namespace App\Policies;

use App\Models\Cours;
use App\Models\Utilisateur;

class CoursPolicy
{
    public function viewAny(Utilisateur $utilisateur): bool
    {
        // Un adhérent voit le planning de sa salle, un staff aussi.
        return true;
    }

    public function view(Utilisateur $utilisateur, Cours $cours): bool
    {
        return $this->appartientMemeSalle($utilisateur, $cours->id_salle);
    }

    public function create(Utilisateur $utilisateur): bool
    {
        return $utilisateur->hasAnyRole(['gerant', 'super_admin']);
    }

    public function update(Utilisateur $utilisateur, Cours $cours): bool
    {
        return $utilisateur->hasAnyRole(['gerant', 'super_admin'])
            && $this->appartientMemeSalle($utilisateur, $cours->id_salle);
    }

    public function delete(Utilisateur $utilisateur, Cours $cours): bool
    {
        return $this->update($utilisateur, $cours);
    }

    /**
     * Vérifie que le staff connecté appartient bien à la salle concernée.
     * Le moindre privilège s'applique : un coach de la salle A ne touche
     * jamais aux données de la salle B, même s'il connaît l'ID.
     */
    private function appartientMemeSalle(Utilisateur $utilisateur, int $idSalle): bool
    {
        if ($utilisateur->hasRole('super_admin')) {
            return true; // à restreindre encore si vous gérez plusieurs plateformes
        }

        return $utilisateur->staff?->id_salle === $idSalle;
    }
}