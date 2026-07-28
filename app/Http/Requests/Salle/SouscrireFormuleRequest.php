<?php

namespace App\Http\Requests\Salle;

use Illuminate\Foundation\Http\FormRequest;

class SouscrireFormuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul un adherent souscrit pour lui-meme — jamais pour un tiers
        // via cet endpoint (le staff a sa propre route dediee, scopee).
        return $this->user()?->type_utilisateur === 'adherent';
    }

    public function rules(): array
    {
        return [
            'moyen_paiement' => ['required', 'in:wave,orange_money,free_money,especes'],
        ];
    }
}
