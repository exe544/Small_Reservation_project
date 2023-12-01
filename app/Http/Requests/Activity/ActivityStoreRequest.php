<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'min:3', 'max:1000'],
            'start_date' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:1'],
            'image' => ['image', 'nullable', 'max:1024'],
            'guide_id' => ['required', Rule::exists('users', 'id')],
        ];
    }
}
