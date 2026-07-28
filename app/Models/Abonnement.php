<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $table = 'abonnements';
    protected $primaryKey = 'id_abonnement';

    // qr_token est volontairement hors $fillable : généré uniquement par
    // QrTokenService::generer() via forceFill(), jamais depuis une requête
    // cliente (même principe que statut_paiement sur Paiement).
    protected $fillable = ['id_adherent', 'id_formule', 'date_debut', 'date_fin', 'statut'];

    protected function casts(): array
    {
        return ['date_debut' => 'date', 'date_fin' => 'date'];
    }

    public function adherent()
    {
        return $this->belongsTo(Adherent::class, 'id_adherent', 'id_adherent');
    }

    public function formule()
    {
        return $this->belongsTo(FormuleAbonnement::class, 'id_formule', 'id_formule');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_abonnement', 'id_abonnement');
    }

    /**
     * Salle de cet abonnement, accessible directement sans passer par
     * ->formule->salle dans les contrôleurs/vues.
     */
    public function salle()
    {
        return $this->formule?->salle;
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif' && $this->date_fin?->isFuture();
    }
}