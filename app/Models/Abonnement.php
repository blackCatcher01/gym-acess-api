<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $table = 'abonnements';
    protected $primaryKey = 'id_abonnement';

    protected $fillable = ['id_adherent', 'id_formule', 'date_debut', 'date_fin', 'statut'];

    protected function casts(): array
    {
        return ['date_debut' => 'date', 'date_fin' => 'date'];
    }

    public function adherent()
    {
        return $this->belongsTo(Adherent::class, 'id_adherent', 'id_adherent');
    }

    public function formule()
    {
        return $this->belongsTo(FormuleAbonnement::class, 'id_formule', 'id_formule');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_abonnement', 'id_abonnement');
    }
}