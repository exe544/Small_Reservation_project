<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_activity_page(): void
    {
        $activity = Activity::factory()->withCompanyAndGuide()->create();

        $response = $this->get(route('activity.show', $activity));

        $guide = User::find($activity->guide_id);

        $response->assertStatus(200);
        $response->assertSeeText([
            $activity->name,
            $activity->description,
            $activity->price,
            $guide->name,
            $guide->email,
            ]);
    }

    public function test_404_view_for_unexisting_activity_page(): void
    {
        $response = $this->get(route('activity.show', 69));

        $response->assertNotFound();
    }
}
