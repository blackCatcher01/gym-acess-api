<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id_notification';

    protected $fillable = ['id_utilisateur', 'id_abonnement', 'type_notification', 'canal', 'contenu', 'date_envoi', 'statut_envoi'];

    protected function casts(): array
    {
        return [
            'contenu' => 'encrypted',
            'date_envoi' => 'datetime',
        ];
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class, 'id_abonnement', 'id_abonnement');
    }
}