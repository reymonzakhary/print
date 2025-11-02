<?php

namespace Modules\Cms\Database\Seeders;

use Hyn\Tenancy\Environment;
use Illuminate\Database\Seeder;

class CmsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
//        Model::unguard();
        $this->command->info("Tenant: System");
        $this->call(VariablesTableSeeder::class);
        $this->call(ResourceTypeTableSeeder::class);

        collect(tenants())->map(function ($tenant) {
            $this->command->info("Tenant: " . $tenant->website->uuid);
            app(Environment::class)->tenant($tenant->website);
            $this->call(VariablesTableSeeder::class);
            $this->call(ResourceTypeTableSeeder::class);
        });
    }
}
