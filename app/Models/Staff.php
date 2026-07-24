<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'staff';
    protected $primaryKey = 'id_staff';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_salle',
        'role_staff',
        'date_embauche',
    ];

    protected function casts(): array
    {
        return [
            'date_embauche' => 'date',
        ];
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_staff', 'id_utilisateur');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }

    public function coursAnimes()
    {
        return $this->hasMany(Cours::class, 'id_staff', 'id_staff');
    }

    public function journalAudit()
    {
        return $this->hasMany(JournalAudit::class, 'id_staff', 'id_staff');
    }
}