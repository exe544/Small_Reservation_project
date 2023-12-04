<?php

namespace App\Rules;

use App\Models\RegistrationInvitation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RegistrationLinkWasSent implements ValidationRule
{

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (RegistrationInvitation::where('email', $value)->exists()){
            $fail('Invitation with this email address already requested.');
        }
    }
}
