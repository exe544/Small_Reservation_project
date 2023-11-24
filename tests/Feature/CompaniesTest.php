<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompaniesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_companies_index_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('companies.index'));

        $response->assertStatus(200);
    }

    public function test_customer_can_access_companies_index_page(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->get(route('companies.index'));

        $response->assertStatus(403);
    }

}
