<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use SoftDeletes;

    protected $table = 'produits';
    protected $primaryKey = 'id_produit';

    protected $fillable = ['id_boutique', 'id_categorie', 'nom', 'description', 'prix', 'image', 'stock', 'actif'];

    protected function casts(): array
    {
        return ['prix' => 'decimal:2', 'actif' => 'boolean'];
    }

    public function boutique()
    {
        return $this->belongsTo(BoutiquePartenaire::class, 'id_boutique', 'id_boutique');
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieProduit::class, 'id_categorie', 'id_categorie');
    }
}
