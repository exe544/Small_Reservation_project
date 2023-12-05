<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Activity;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_auth_user_can_view_home_page(): void
    {
       $response = $this->get(route('home'));

       $response->assertStatus(200);
    }

    public function test_auth_user_can_view_home_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
    }

    public function test_show_no_activities_when_theres_no_upcoming_activities(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSeeText('No activities');
    }

    public function test_pagination_isnt_shown_when_have_less_than_10_activities(): void
    {
        $company = Company::factory()->create();
        $guide = User::factory()->create(['company_id' => $company->id, 'role_id' => Role::GUIDE->value]);
        Activity::factory(9)->create(['company_id' => $company->id, 'guide_id' => $guide->id]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Next');
    }

    public function test_pagination_shown_when_have_10_activities(): void
    {
        $company = Company::factory()->create();
        $guide = User::factory()->create(['company_id' => $company->id, 'role_id' => Role::GUIDE->value]);
        Activity::factory(9)->create(['company_id' => $company->id, 'guide_id' => $guide->id]);
        $tenthActivity = Activity::factory()->create(['company_id' => $company->id, 'guide_id' => $guide->id]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSeeText('Next');

        $response = $this->get(route('home') . '/?page=2');
        $response->assertStatus(200);
        $response->assertSeeText($tenthActivity->name);
    }

    public function test_newest_order_of_activities(): void
    {
        $company = Company::factory()->create();
        $guide = User::factory()->create(['company_id' => $company->id, 'role_id' => Role::GUIDE->value]);
        $activity1 = Activity::factory()->create(['company_id' => $company->id, 'guide_id' => $guide->id, 'start_date' => now()->addWeek()]);
        $activity2 = Activity::factory()->create(['company_id' => $company->id, 'guide_id' => $guide->id, 'start_date' => now()->addWeeks(2)]);
        $activity3 = Activity::factory()->create(['company_id' => $company->id, 'guide_id' => $guide->id, 'start_date' => now()->addWeeks(3)]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSeeTextInOrder([
            $activity1->name,
            $activity2->name,
            $activity3->name,
        ]);
    }
}
