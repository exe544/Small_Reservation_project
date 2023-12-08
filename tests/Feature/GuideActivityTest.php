<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_export()
    {
        $guide = User::factory()->guide()->withCompany()->create();
        $activity = Activity::factory()->create(['guide_id' => $guide->id, 'company_id' => $guide->company_id]);

        $response = $this->actingAs($guide)->get(route('guide-activity.export', $activity));

        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="' . $activity->name .'.pdf"', $response->headers->get('Content-Disposition'));
    }
}
