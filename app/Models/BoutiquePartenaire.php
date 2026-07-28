<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoutiquePartenaire extends Model
{
    use SoftDeletes;

    protected $table = 'boutiques_partenaires';
    protected $primaryKey = 'id_boutique';

    protected $fillable = ['nom', 'description', 'logo', 'telephone_contact', 'adresse', 'ville', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function produits()
    {
        return $this->hasMany(Produit::class, 'id_boutique', 'id_boutique');
    }
}
