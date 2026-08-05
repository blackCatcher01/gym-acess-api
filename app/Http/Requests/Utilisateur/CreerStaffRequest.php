<?php

namespace App\Http\Requests\Utilisateur;

use Illuminate\Foundation\Http\FormRequest;

class CreerStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['gerant', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/', 'unique:utilisateurs,telephone'],
            'role_staff' => ['required', 'in:coach,gerant'],
            // Requis uniquement pour le super_admin (un gerant cree
            // toujours dans sa propre salle — verifie dans le controleur).
            'id_salle' => ['sometimes', 'integer', 'exists:salles,id_salle'],
            'date_embauche' => ['nullable', 'date'],
        ];
    }
}