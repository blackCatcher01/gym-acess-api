<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentreInteret extends Model
{
    protected $table = 'centres_interet';
    protected $primaryKey = 'id_centre_interet';

    protected $fillable = ['nom', 'icone'];

    public function utilisateurs()
    {
        return $this->belongsToMany(
            Utilisateur::class,
            'utilisateur_centre_interet',
            'id_centre_interet',
            'id_utilisateur'
        );
    }
}
