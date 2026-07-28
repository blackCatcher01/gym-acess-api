<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salle extends Model
{
    use SoftDeletes;

    protected $table = 'salles';
    protected $primaryKey = 'id_salle';

    protected $fillable = [
        'nom_salle',
        'adresse',
        'ville',
        'telephone_contact',
    ];

    /**
     * Adherents ayant un abonnement (actif ou passe) dans cette salle —
     * plus de colonne id_salle directe sur adherents depuis la Phase 1
     * (un adherent n'appartient plus a une seule salle fixe).
     */
    public function adherents()
    {
        return Adherent::whereHas('abonnements.formule', fn ($q) => $q->where('id_salle', $this->id_salle));
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'id_salle', 'id_salle');
    }

    public function formulesAbonnement()
    {
        return $this->hasMany(FormuleAbonnement::class, 'id_salle', 'id_salle');
    }

    public function cours()
    {
        return $this->hasMany(Cours::class, 'id_salle', 'id_salle');
    }
}