<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // accessible avant authentification, throttle:login gère l'abus
    }

    public function rules(): array
    {
        return [
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'purpose' => ['sometimes', 'in:login,reset,verify_phone'],
            'nom' => ['sometimes', 'string', 'max:100'],
        ];
    }
}