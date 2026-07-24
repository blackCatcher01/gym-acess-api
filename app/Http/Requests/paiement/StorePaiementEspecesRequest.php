<?php

namespace App\Http\Requests\Paiement;

use Illuminate\Foundation\Http\FormRequest;

class StorePaiementEspecesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul le staff (coach/gérant) peut enregistrer un paiement espèces —
        // jamais l'adhérent lui-même (section 6.6 : le statut "payé" ne
        // vient jamais d'une déclaration cliente non vérifiée).
        return $this->user()?->hasAnyRole(['coach', 'gerant', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'id_abonnement' => ['required', 'integer', 'exists:abonnements,id_abonnement'],
            'montant' => ['required', 'numeric', 'min:0'],
        ];
    }
}