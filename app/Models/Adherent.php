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

    /**
     * id_adherent (= id_utilisateur) est volontairement hors $fillable —
     * un client ne doit jamais pouvoir choisir son propre ID. Mais ça veut
     * aussi dire qu'un simple Adherent::create([...'id_adherent' => X]) ou
     * ->update(['id_adherent' => X]) échoue SILENCIEUSEMENT (Laravel ignore
     * l'attribut sans erreur). Passez toujours par cette méthode pour créer
     * un Adherent, plutôt que par Adherent::create() directement.
     */
    public static function creerPourUtilisateur(Utilisateur $utilisateur, array $attributs): self
    {
        $adherent = new self($attributs);
        $adherent->forceFill(['id_adherent' => $utilisateur->id_utilisateur]);
        $adherent->save();

        return $adherent;
    }

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