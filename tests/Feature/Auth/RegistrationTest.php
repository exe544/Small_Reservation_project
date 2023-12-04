<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\Company;
use App\Models\RegistrationInvitation;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => Role::CUSTOMER->value,
            'company_id' => null,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_user_can_register_as_company_owner_via_token()
    {
        $company = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $this->actingAs($user)->post(route('companies.users.store', $company), ['email' => 'test@test.com']);

        $this->assertDatabaseHas('registration_invitations', [
            'email' => 'test@test.com',
        ]);

        $invitation = RegistrationInvitation::where('email', 'test@test.com')->first();

        Auth::logout();

        $response = $this->withSession(['invitation_token' => $invitation['token']])->post('/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'role_id' => Role::COMPANY_OWNER->value,
            'company_id' => $company->id,
        ]);
    }

    public function test_user_can_register_as_guide_owner_via_token()
    {
        $company = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $this->actingAs($user)->post(route('companies.guides.store', $company), ['email' => 'guide@test.com']);

        $this->assertDatabaseHas('registration_invitations', [
            'email' => 'guide@test.com',
        ]);

        $invitation = RegistrationInvitation::where('email', 'guide@test.com')->first();

        Auth::logout();

        $response = $this->withSession(['invitation_token' => $invitation['token']])->post('/register', [
            'name' => 'Test User',
            'email' => 'guide@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'guide@test.com',
            'role_id' => Role::GUIDE->value,
            'company_id' => $company->id,
        ]);
    }

    public function test_user_can_register_with_another_email_via_token()
    {
        $company = Company::factory()->create();
        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $this->actingAs($user)->post(route('companies.guides.store', $company), ['email' => 'guide@test.com']);

        $this->assertDatabaseHas('registration_invitations', [
            'email' => 'guide@test.com',
        ]);

        $invitation = RegistrationInvitation::where('email', 'guide@test.com')->first();

        Auth::logout();

        $response = $this->withSession(['invitation_token' => $invitation['token']])->post('/register', [
            'name' => 'Test User',
            'email' => 'guide1@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertInvalid(['invitation' => 'Invitation link does not match email indicated!']);
    }
}
