<?php

declare(strict_types=1);

namespace App\Http\Requests\Guide;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class GuideStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::unique('users', 'email'), 'unique:registration_invitations,email'],
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Invitation with this email address already requested.'
        ];
    }
}
