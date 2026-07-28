<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieProduit extends Model
{
    protected $table = 'categories_produits';
    protected $primaryKey = 'id_categorie';

    protected $fillable = ['nom', 'slug'];

    public function produits()
    {
        return $this->hasMany(Produit::class, 'id_categorie', 'id_categorie');
    }
}
