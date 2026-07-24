<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
    protected $table = 'trusted_devices';
    protected $primaryKey = 'id_device';

    protected $fillable = ['id_utilisateur', 'device_token', 'device_name', 'derniere_utilisation', 'revoque'];

    protected function casts(): array
    {
        return ['derniere_utilisation' => 'datetime', 'revoque' => 'boolean'];
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
}