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

    public function adherents()
    {
        return $this->hasMany(Adherent::class, 'id_salle', 'id_salle');
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