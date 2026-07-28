<?php

namespace App\Http\Requests\Profil;

use Illuminate\Foundation\Http\FormRequest;

class CompleterProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'date_naissance' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'sexe' => ['required', 'in:homme,femme,autre'],
            'comment_connu' => ['nullable', 'string', 'max:100'],
            'centres_interet' => ['sometimes', 'array'],
            'centres_interet.*' => ['integer', 'exists:centres_interet,id_centre_interet'],
        ];
    }
}
