<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'date_naissance',
        'sexe',
        'comment_connu',
        'type_utilisateur',
        'photo',
        'is_active',
    ];

    protected $hidden = [
        'mot_de_passe_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'profil_complete' => 'boolean',
            'date_naissance' => 'date',
            'last_login_at' => 'datetime',
            'mot_de_passe_hash' => 'hashed',
        ];
    }

    public function adherent()
    {
        return $this->hasOne(Adherent::class, 'id_adherent', 'id_utilisateur');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'id_staff', 'id_utilisateur');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function centresInteret()
    {
        return $this->belongsToMany(
            CentreInteret::class,
            'utilisateur_centre_interet',
            'id_utilisateur',
            'id_centre_interet'
        );
    }

    public function nomComplet(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }
}