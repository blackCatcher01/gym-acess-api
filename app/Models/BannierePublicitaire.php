<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannierePublicitaire extends Model
{
    protected $table = 'bannieres_publicitaires';
    protected $primaryKey = 'id_banniere';

    protected $fillable = ['titre', 'image', 'lien_url', 'ordre_affichage', 'actif', 'date_debut', 'date_fin'];

    protected function casts(): array
    {
        return ['actif' => 'boolean', 'date_debut' => 'date', 'date_fin' => 'date'];
    }

    public function scopeActives($query)
    {
        return $query->where('actif', true)
            ->where(fn ($q) => $q->whereNull('date_debut')->orWhere('date_debut', '<=', now()))
            ->where(fn ($q) => $q->whereNull('date_fin')->orWhere('date_fin', '>=', now()))
            ->orderBy('ordre_affichage');
    }
}
