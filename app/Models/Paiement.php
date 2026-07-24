<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'paiements';
    protected $primaryKey = 'id_paiement';

    // statut_paiement volontairement absent : ne doit être modifié
    // que par le webhook opérateur, jamais par une requête utilisateur.
    protected $fillable = [
        'id_abonnement',
        'montant',
        'moyen_paiement',
        'reference_transaction',
        'date_paiement',
    ];

    protected function casts(): array
    {
        return ['montant' => 'decimal:2', 'date_paiement' => 'datetime'];
    }

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class, 'id_abonnement', 'id_abonnement');
    }
}