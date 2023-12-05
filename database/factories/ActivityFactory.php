<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->text(200),
            'start_date' => Carbon::now()->addDays(1),
            'price' => random_int(min: 10, max: 10000),
            'photo' => null,
        ];
    }

    public function withPhotoName(): Factory
    {
        $image = UploadedFile::fake()->image($this->faker->text(12) . '.jpg')->size(500);
        $fileName = $image->store(options: 'activities');

        //add thumbnail
        $img = Image::make(Storage::disk('activities')->get($fileName))
            ->resize(270, 270, function ($constraint) {
            $constraint->aspectRatio();
        });
        Storage::disk('activities')->put('thumbs/' . $image->hashName(), $img->stream());

        return $this->state(function (array $attributes) use ($fileName) {
           return [
               'photo' => $fileName,
           ];
        });
    }

    public function withCompanyAndGuide(): Factory
    {
        $company = Company::factory()->create();
        $guide = User::factory()->create(['role_id' => Role::GUIDE->value, 'company_id' => $company->id]);

        return $this->state(function (array $attributes) use ($guide) {
            return [
                'company_id' => $guide->company_id,
                'guide_id' => $guide->id,
            ];
        });
    }
}
