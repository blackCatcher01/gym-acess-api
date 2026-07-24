<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $table = 'cours';
    protected $primaryKey = 'id_cours';

    protected $fillable = ['id_salle', 'id_staff', 'nom_cours', 'date_heure_debut', 'duree_min', 'capacite_max'];

    protected function casts(): array
    {
        return ['date_heure_debut' => 'datetime'];
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }

    public function coach()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'id_cours', 'id_cours');
    }

    public function placesRestantes(): int
    {
        return $this->capacite_max - $this->reservations()->where('statut_reservation', 'confirmee')->count();
    }
}