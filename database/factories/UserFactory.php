<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => Role::CUSTOMER->value,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) =>[
            'role_id' => Role::ADMINISTRATOR->value,
        ]);
    }

    public function company_owner(): static
    {
        return $this->state(fn(array $attributes) =>[
            'role_id' => Role::COMPANY_OWNER->value,
        ]);
    }

    public function guide(): static
    {
        return $this->state(fn(array $attributes) =>[
            'role_id' => Role::GUIDE->value,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function simplePassword(): static
    {
        return $this->state(fn(array $attributes) =>[
            'password' => Hash::make('123456789'),
        ]);
    }

    public function withCompany(): static
    {
        $company = Company::factory()->create();
        return $this->state(fn(array $attributes) =>[
            'company_id' => $company->id,
        ]);
    }
}
