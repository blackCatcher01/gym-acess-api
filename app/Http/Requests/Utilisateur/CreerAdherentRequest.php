<?php

namespace App\Http\Requests\Utilisateur;

use Illuminate\Foundation\Http\FormRequest;

class CreerAdherentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['coach', 'gerant', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/', 'unique:utilisateurs,telephone'],
            'date_naissance' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'sexe' => ['required', 'in:homme,femme,autre'],
            'email' => ['nullable', 'email', 'max:150', 'unique:utilisateurs,email'],
        ];
    }
}