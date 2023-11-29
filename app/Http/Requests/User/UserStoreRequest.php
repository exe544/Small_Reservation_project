<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:35'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::default()],
        ];
    }
}
