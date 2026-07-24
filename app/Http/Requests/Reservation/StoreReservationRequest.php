<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul un adhérent connecté peut réserver pour lui-même —
        // id_adherent n'est jamais pris depuis le payload client.
        return $this->user()?->type_utilisateur === 'adherent';
    }

    public function rules(): array
    {
        return [
            'id_cours' => ['required', 'integer', 'exists:cours,id_cours'],
        ];
    }
}