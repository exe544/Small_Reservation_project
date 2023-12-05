<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Activity;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{

    public function run(): void
    {
        $company = Company::factory()->create(['name' => 'Seedered Company']);
        $guide = User::factory()->create([
            'name'=> 'Best Guide Name',
            'company_id' => $company->id,
            'role_id' => Role::GUIDE->value]);

        Activity::factory(9)->create(['company_id' => $company->id, 'guide_id' => $guide->id]);
    }
}
