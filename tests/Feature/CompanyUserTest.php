<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function Laravel\Prompts\password;

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

    public function test_company_owner_can_view_his_company_users(): void
    {
        $company = Company::factory()->create();
        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $companyOwner2 = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->get(route('companies.users.index', $company->id));

        $response->assertStatus(200)
            ->assertSeeText($companyOwner2->name);
    }

    public function test_company_owner_cant_view_other_company_users(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->get(route('companies.users.index', $company2->id));

        $response->assertStatus(403);
    }

    public function test_company_owner_can_create_other_users_for_his_company(): void
    {
        $company = Company::factory()->create();
        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $companyOwnerNew = User::factory()->make()->toArray();
        $companyOwnerNew['password'] = "123456789";

        $response = $this->actingAs($companyOwner)->post(route('companies.users.store', $company->id), $companyOwnerNew);

        $response->assertRedirectToRoute('companies.users.index', $company);

        $this->assertDatabaseHas('users', [
           'name' => $companyOwnerNew['name'],
           'email' => $companyOwnerNew['email'],
           'company_id' => $companyOwner->company_id,
            'role_id' => Role::COMPANY_OWNER->value,
        ]);
    }
    public function test_company_owner_cannot_create_users_for_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $companyOwnerNew = User::factory()->make()->toArray();
        $companyOwnerNew['password'] = "123456789";

        $response = $this->actingAs($companyOwner)->post(route('companies.users.store', $company2->id), $companyOwnerNew);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'name' => $companyOwnerNew['name'],
            'email' => $companyOwnerNew['email'],
            'company_id' => $companyOwner->company_id,
        ]);
    }

    public function test_company_owner_can_update_users_from_his_company(): void
    {
        $company = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $companyUser = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->put(route('companies.users.update', [$company->id, $companyUser->id]), [
            'name' => 'new name',
            'email' => 'new_email@example.com'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', [
            'name' => $companyUser['name'],
            'email' => $companyUser['email'],
            'company_id' => $companyUser->company_id,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'new name',
            'email' => 'new_email@example.com',
            'company_id' => $companyUser->company_id,
        ]);
    }

    public function test_company_owner_cannot_update_users_from_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $companyUser = User::factory()->companyOwner()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($companyOwner)->put(route('companies.users.update', [$company2->id, $companyUser->id]), [
            'name' => 'new name',
            'email' => 'new_email@example.com'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'name' => $companyUser['name'],
            'email' => $companyUser['email'],
            'company_id' => $companyUser->company_id,
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'new name',
            'email' => 'new_email@example.com',
            'company_id' => $companyUser->company_id,
        ]);
    }

    public function test_company_owner_can_delete_user_from_his_company(): void
    {
        $company = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $companyUser = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->delete(route('companies.users.destroy', [$company->id, $companyUser->id]));

        $response->assertRedirectToRoute('companies.users.index', $company);

        $this->assertSoftDeleted($companyUser);
    }

    public function test_company_owner_cannot_delete_user_from_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $companyUser = User::factory()->companyOwner()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($companyOwner)->delete(route('companies.users.destroy', [$company2->id, $companyUser->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'name' => $companyUser['name'],
            'email' => $companyUser['email'],
            'company_id' => $companyUser->company_id,
            'deleted_at' => null,
        ]);

    }
}
