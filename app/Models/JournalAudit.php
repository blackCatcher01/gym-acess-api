<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalAudit extends Model
{
    protected $table = 'journal_audit';
    protected $primaryKey = 'id_audit';

    // Écrit exclusivement côté serveur (jamais via une requête entrante) :
    // pas de $fillable exposé, on utilise ::create() uniquement en interne.
    protected $guarded = ['id_audit'];

    protected function casts(): array
    {
        return [
            'details' => 'encrypted',
            'date_action' => 'datetime',
        ];
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle', 'id_salle');
    }
}