<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

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
        $image = UploadedFile::fake()->image('avatar.jpg')->size(500);
        $fileName = $image->store(options: 'activities');

        return $this->state(function (array $attributes) use ($fileName) {
           return [
               'photo' => $fileName,
           ];
        });
    }
}
