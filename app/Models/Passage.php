<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passage extends Model
{
    protected $table = 'passages';
    protected $primaryKey = 'id_passage';

    // resultat/motif_refus fixés par le serveur au moment du scan,
    // jamais envoyés tels quels par le client.
    protected $fillable = ['id_adherent', 'id_salle', 'id_cours'];

    protected function casts(): array
    {
        return ['date_heure_passage' => 'datetime'];
    }

    public function adherent()
    {
        return $this->belongsTo(Adherent::class, 'id_adherent', 'id_adherent');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'id_cours', 'id_cours');
    }
}