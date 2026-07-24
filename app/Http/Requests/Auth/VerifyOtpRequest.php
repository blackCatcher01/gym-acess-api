<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'code' => ['required', 'digits:6'],
            'purpose' => ['sometimes', 'in:login,reset,verify_phone'],
            'nom' => ['sometimes', 'string', 'max:100'],
        ];
    }
}