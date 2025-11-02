<?php

namespace Database\Seeders\Tenants;

use Illuminate\Database\Seeder;

class TenantTableSeeder extends Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StatusSeeder::class);
        $this->call(LexiconSeeder::class);
        $this->call(ContextSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(DesignProvidersSeeder::class);
        $this->call(LaratrustSeeder::class);
        $this->call(TypeSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(UserSettingSeeder::class);
        $this->call(PrintingMethods::class);
    }
}
