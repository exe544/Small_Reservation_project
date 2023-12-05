<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Collection\Collection;
use Tests\TestCase;

class CompanyActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_owner_can_view_only_his_company_activities(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $activity = Activity::factory()->create(['company_id' => $company->id, 'guide_id' => $guide->id]);
        $activity2 = Activity::factory()->create([
            'company_id' => $company2->id,
            'guide_id' => User::factory()->guide()->create(['company_id' => $company2->id]),
        ]);

        $response = $this->actingAs($owner)->get(route('companies.activities.index', $company));

        $response->assertStatus(200);
        $response->assertSeeText([$activity['name'], $activity['start_date']]);
        $response->assertDontSee($activity2['name']);
    }

    public function test_company_owner_can_create_activities_for_his_company(): void
    {
        Storage::fake('activities');
        $file = UploadedFile::fake()->image('avatar.jpg')->size(500);

        $company = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $activity = Activity::factory()->make(['guide_id' => $guide->id, 'image' => $file])->toArray();

        $this->actingAs($owner)->post(route('companies.activities.store', $company), $activity);

        Storage::disk('activities')->assertExists($file->hashName());

        $this->assertDatabaseHas('activities', [
            'name' => $activity['name'],
            'description' => $activity['description'],
            'start_date' => $activity['start_date'],
            'price' => $activity['price']*100,
        ]);
    }

    public function test_cannot_upload_not_image_file(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.pdf', 2000, 'application/pdf');

        $company = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $activity = Activity::factory()->make(['guide_id' => $guide->id, 'image' => $file])->toArray();

        $response = $this->actingAs($owner)->post(route('companies.activities.store', $company), $activity);

        $response->assertSessionHasErrors(['image']);
        Storage::disk('public')->assertMissing('activities/'. $file->hashName());
    }

    public function test_guides_are_shown_only_for_specific_company_in_create_form(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $guide2 = User::factory()->guide()->create(['company_id' => $company2->id]);

        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($owner)->get(route('companies.activities.create', $company));

        $response->assertStatus(200);

        //assert that list doesn't have unrelated guids to the company
        $response->assertViewHas('guides', function (\Illuminate\Support\Collection $guides) use ($guide2) {
           return !array_key_exists($guide2->id, $guides->toArray());
        });

        //assert that list with name = ids guids are from the company
        $response->assertViewHas('guides', function (\Illuminate\Support\Collection $guides) use ($guide) {
            return $guide->name === $guides[$guide->id];
        });
    }

    public function test_guides_are_shown_only_for_specific_company_in_edit_form(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $guide2 = User::factory()->guide()->create(['company_id' => $company2->id]);
        $activity = Activity::factory()->create(['company_id' => $company->id, 'guide_id' => $guide]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $response = $this->actingAs($owner)->get(route('companies.activities.edit', [$company, $activity]));

        $response->assertStatus(200);

        //assert that list doesn't have unrelated guids to the company
        $response->assertViewHas('guides', function (\Illuminate\Support\Collection $guides) use ($guide2) {
            return !array_key_exists($guide2->id, $guides->toArray());
        });

        //assert that list with name = ids guids are from the company
        $response->assertViewHas('guides', function (\Illuminate\Support\Collection $guides) use ($guide) {
            return $guide->name === $guides[$guide->id];
        });
    }

    public function test_company_owner_cannot_create_activities_for_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $activity = Activity::factory()->make(['guide_id' => $guide->id])->toArray();

        $response = $this->actingAs($owner)->post(route('companies.activities.store', $company2), $activity);

        $response->assertForbidden();
    }

    public function test_company_owner_can_update_activity_for_his_company(): void
    {
        Storage::fake('activities');
        $newFile = UploadedFile::fake()->image('avatar2.jpg')->size(500);

        $company = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $activityOld = Activity::factory()->withPhotoName()->create(['company_id' => $company->id, 'guide_id' => $guide->id]);

        $activityNew = Activity::factory()->make(['guide_id' => $guide->id, 'image' => $newFile])->toArray();

        $response = $this->actingAs($owner)->put(route('companies.activities.update', [$company, $activityOld]), $activityNew);


        $response->assertRedirectToRoute('companies.activities.index', $company);
        Storage::disk('activities')->assertExists($newFile->hashName());

        //assert that old image was removed from storage
        Storage::disk('activities')->assertMissing($activityOld->photo);


        $this->assertDatabaseHas('activities', [
            'name' => $activityNew['name'],
            'description' => $activityNew['description'],
            'start_date' => $activityNew['start_date'],
            'price' => $activityNew['price']*100,
        ]);
    }

    public function test_company_owner_cannot_update_activity_from_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company2->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);

        $activityOld = Activity::factory()->create(['company_id' => $company2->id, 'guide_id' => $guide->id]);
        $activityNew = Activity::factory()->make(['guide_id' => $guide->id])->toArray();

        $response = $this->actingAs($owner)->put(route('companies.activities.update', [$company2, $activityOld]), $activityNew);

        $response->assertForbidden();
    }

    public function test_company_owner_cannot_delete_activity_from_other_company(): void
    {
        $company = Company::factory()->create();
        $company2 = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company2->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $activityOld = Activity::factory()->create(['company_id' => $company2->id, 'guide_id' => $guide->id]);

        $response = $this->actingAs($owner)->delete(route('companies.activities.update', [$company2, $activityOld]));

        $response->assertForbidden();
    }

    public function test_company_owner_can_delete_activity_from_his_company(): void
    {
        $company = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $activity = Activity::factory()->create(['company_id' => $company->id, 'guide_id' => $guide->id]);

        $this->actingAs($owner)->delete(route('companies.activities.update', [$company, $activity]));

        $this->assertModelMissing($activity);
    }

    public function test_image_delete_from_storage_when_delete_activity_from__company(): void
    {
        Storage::fake('public');
        $company = Company::factory()->create();
        $guide = User::factory()->guide()->create(['company_id' => $company->id]);
        $owner = User::factory()->companyOwner()->create(['company_id' => $company->id]);
        $activity = Activity::factory()->withPhotoName()->create(['company_id' => $company->id, 'guide_id' => $guide->id]);

        $this->actingAs($owner)->delete(route('companies.activities.update', [$company, $activity]));

        Storage::disk('public')->assertMissing($activity->photo);
        $this->assertModelMissing($activity);
    }
}
