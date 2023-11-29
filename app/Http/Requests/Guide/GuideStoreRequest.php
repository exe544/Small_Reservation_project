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
            'name' => ['required', 'string', 'min:2', 'max:55'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', Password::default()],
        ];
    }
}
