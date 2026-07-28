<?php

namespace App\Http\Requests\Salle;

use Illuminate\Foundation\Http\FormRequest;

class CreerAbonnementAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['coach', 'gerant', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'telephone' => ['required', 'string'],
            'id_formule' => ['required', 'integer', 'exists:formules_abonnement,id_formule'],
            'moyen_paiement' => ['required', 'in:wave,orange_money,free_money,especes'],
        ];
    }
}
