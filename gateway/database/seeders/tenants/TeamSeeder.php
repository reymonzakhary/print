<?php

declare(strict_types=1);

namespace Database\Seeders\tenants;

use App\Models\Tenants\Company;
use App\Models\Tenants\Team;
use Faker\Factory;
use Illuminate\Database\Seeder;

final class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();

        # Global Teams
        for ($i = 0; $i <= 3; $i++) {
            Team::create(['name' => $faker->city()]);
        }

        # Company Teams
        for ($j = 0; $j <= 5; $j++) {
            $company = Company::create(['name' => $faker->company()]);

            for ($k = 0; $k <= 3; $k++) {
                Team::create(['name' => $faker->city(), 'company_id' => $company->id]);
            }
        }
    }
}
