<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adherent extends Model
{
    use SoftDeletes;

    protected $table = 'adherents';
    protected $primaryKey = 'id_adherent';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_salle',
        'qr_token',
        'date_inscription',
    ];

    protected function casts(): array
    {
        return [
            'date_inscription' => 'date',
        ];
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_adherent', 'id_utilisateur');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'id_adherent', 'id_adherent');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'id_adherent', 'id_adherent');
    }

    public function passages()
    {
        return $this->hasMany(Passage::class, 'id_adherent', 'id_adherent');
    }
}