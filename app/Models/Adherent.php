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
    public static function creerPourUtilisateur(Utilisateur $utilisateur, array $attributs = []): self
    {
        $adherent = new self(array_merge(['date_inscription' => now()], $attributs));
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

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'id_adherent', 'id_adherent');
    }

    /**
     * Salles où l'adhérent a (ou a eu) un abonnement — plus de salle
     * unique, potentiellement plusieurs en simultané (multi-salles).
     */
    public function salles()
    {
        return Salle::whereIn(
            'id_salle',
            FormuleAbonnement::whereIn(
                'id_formule',
                $this->abonnements()->pluck('id_formule')
            )->pluck('id_salle')
        )->get();
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