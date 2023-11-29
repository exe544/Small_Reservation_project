<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_owner_can_view_his_company_users(): void
    {
        $company = Company::factory()->create();
        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->get(route('companies.guides.index', $company->id));

        $response->assertStatus(200)
            ->assertSeeText($guide->name);
    }

    public function test_company_owner_cannot_view_other_company_users(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->get(route('companies.guides.index', $company2->id));

        $response->assertStatus(403);
    }

    public function test_company_owner_can_create_other_users_for_his_company(): void
    {
        $company = Company::factory()->create();
        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $guide = User::factory()->make()->toArray();
        $guide['password'] = "123456789";

        $response = $this->actingAs($companyOwner)->post(route('companies.guides.store', $company->id), $guide);

        $response->assertRedirectToRoute('companies.guides.index', $company);

        $this->assertDatabaseHas('users', [
            'name' => $guide['name'],
            'email' => $guide['email'],
            'company_id' => $companyOwner->company_id,
            'role_id' => Role::GUIDE->value,
        ]);
    }
    public function test_company_owner_cannot_create_users_for_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $guide = User::factory()->make()->toArray();
        $guide['password'] = "123456789";

        $response = $this->actingAs($companyOwner)->post(route('companies.guides.store', $company2->id), $guide);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', [
            'name' => $guide['name'],
            'email' => $guide['email'],
            'company_id' => $companyOwner->company_id,
        ]);
    }

    public function test_company_owner_can_update_users_from_his_company(): void
    {
        $company = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->put(route('companies.guides.update', [$company->id, $guide->id]), [
            'name' => 'new name',
            'email' => 'new_email@example.com'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', [
            'name' => $guide['name'],
            'email' => $guide['email'],
            'company_id' => $companyOwner->company_id,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'new name',
            'email' => 'new_email@example.com',
            'company_id' => $companyOwner->company_id,
        ]);
    }

    public function test_company_owner_cannot_update_users_from_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $guide = User::factory()->guide()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($companyOwner)->put(route('companies.guides.update', [$company2->id, $guide->id]), [
            'name' => 'new name',
            'email' => 'new_email@example.com'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'name' => $guide['name'],
            'email' => $guide['email'],
            'company_id' => $guide->company_id,
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'new name',
            'email' => 'new_email@example.com',
            'company_id' => $companyOwner->company_id,
        ]);
    }

    public function test_company_owner_can_delete_user_from_his_company(): void
    {
        $company = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);

        $response = $this->actingAs($companyOwner)->delete(route('companies.guides.destroy', [$company->id, $guide->id]));

        $response->assertRedirectToRoute('companies.guides.index', $company);

        $this->assertSoftDeleted($guide);
    }

    public function test_company_owner_cannot_delete_user_from_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();

        $companyOwner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $guide = User::factory()->companyOwner()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($companyOwner)->delete(route('companies.guides.destroy', [$company2->id, $guide->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'name' => $guide['name'],
            'email' => $guide['email'],
            'company_id' => $guide->company_id,
            'deleted_at' => null,
        ]);
    }
}
