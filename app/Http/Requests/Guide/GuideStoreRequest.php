<?php

declare(strict_types=1);

namespace App\Http\Requests\Guide;

use App\Rules\RegistrationLinkWasSent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuideStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::unique('users', 'email'), new RegistrationLinkWasSent()],
        ];
    }

    public function messages()
    {
        return [
            'email.RegistrationLinkWasSent' => 'Invitation with this email address already requested.'
        ];
    }
}
