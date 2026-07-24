<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormuleAbonnement extends Model
{
    protected $table = 'formules_abonnement';
    protected $primaryKey = 'id_formule';

    protected $fillable = ['id_salle', 'nom_formule', 'duree_jours', 'prix', 'actif'];

    protected function casts(): array
    {
        return ['prix' => 'decimal:2', 'actif' => 'boolean'];
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'id_formule', 'id_formule');
    }
}