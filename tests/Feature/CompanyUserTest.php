<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyUserTest extends TestCase
{

    use RefreshDatabase;

    public function test_admin_can_access_company_users_page()
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($admin)->get(route('companies.users.index', $company->id));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_user_for_a_company()
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($admin)->post(route('companies.users.store', $company), [
            'name' => 'test name',
            'email' => 'test@test.com',
            'password' => '12345678'
        ]);

        $response->assertStatus(302);
        $response->assertRedirectToRoute('companies.users.index', $company);

        $this->assertDatabaseHas('users', [
            'name' => 'test name',
            'email' => 'test@test.com',
            'company_id' => $company->id,
            'role_id' => Role::COMPANY_OWNER->value,
        ]);
    }

    public function test_admin_can_create_user_for_a_company_only_with_unique_email()
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $response = $this->actingAs($admin)->post(route('companies.users.store', $company), [
            'name' => 'test name',
            'email' => $admin->email,
        ]);

        $response->assertStatus(302);
        $response->assertInvalid([
            'email' => 'The email has already been taken.',
        ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'test name',
        ]);
    }

    public function test_admin_can_edit_user_for_a_company_only_with_unique_email()
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $user = User::factory()->companyOwner()->create(['name' => 'test', 'email' => 'test@test.com', 'company_id' => $company->id]);

        $response = $this->actingAs($admin)->put(route('companies.users.update', [$company, $user]), [
            'name' => 'test name',
            'email' => $admin->email,
        ]);

        $response->assertStatus(302);
        $response->assertInvalid([
            'email' => 'The email has already been taken.',
        ]);
        $this->assertDatabaseMissing('users', [
            'name' => 'test name',
        ]);
    }

    public function test_admin_can_edit_user_for_a_company()
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $user = User::factory()->companyOwner()->create(['name' => 'test', 'email' => 'test@test.com', 'company_id' => $company->id]);

        $response = $this->actingAs($admin)->put(route('companies.users.update', [$company, $user]), [
            'name' => 'test name',
            'email' => 'test1@test.com',
        ]);

        $response->assertStatus(302);
        $response->assertRedirectToRoute('companies.users.index', $company);

        $this->assertDatabaseHas('users', [
            'name' => 'test name',
            'email' => 'test1@test.com',
            'company_id' => $company->id,
            'role_id' => Role::COMPANY_OWNER->value,
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'test',
            'email' => 'test@test.com',
        ]);
    }

    public function test_admin_can_edit_user_for_a_company_with_old_email()
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $user = User::factory()->companyOwner()->create(['name' => 'test', 'company_id' => $company->id]);

        $this->actingAs($admin)->put(route('companies.users.update', [$company, $user]), [
            'name' => 'test name',
            'email' => $user->email,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'test name',
            'email' => $user->email,
        ]);
    }

    public function test_admin_can_delete_user_for_a_company()
    {
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();

        $user = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($admin)->delete(route('companies.users.destroy', [$company, $user]));

        $response->assertRedirectToRoute('companies.users.index', $company);

        $this->assertSoftDeleted($user);
    }
}
