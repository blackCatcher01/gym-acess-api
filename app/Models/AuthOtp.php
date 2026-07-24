<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthOtp extends Model
{
    protected $table = 'auth_otps';
    protected $primaryKey = 'id_otp';

    protected $guarded = ['id_otp'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }
}