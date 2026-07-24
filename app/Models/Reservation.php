<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'id_reservation';

    protected $fillable = ['id_adherent', 'id_cours', 'statut_reservation', 'date_reservation'];

    protected function casts(): array
    {
        return ['date_reservation' => 'datetime'];
    }

    public function adherent()
    {
        return $this->belongsTo(Adherent::class, 'id_adherent', 'id_adherent');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'id_cours', 'id_cours');
    }
}