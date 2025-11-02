<?php

namespace Database\Seeders\Tenants;

use App\Models\Tenants\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $lanuages = [
            [
                'name' => 'English',
                'iso' => 'en',
            ],
            [
                'name' => 'French',
                'iso' => 'fr',
            ],
            [
                'name' => 'Netherlands',
                'iso' => 'nl',
            ],
            [
                'name' => 'German',
                'iso' => 'de',
            ],
        ];
        foreach ($lanuages as $lanuage) {
            Language::firstOrCreate(['iso' => $lanuage['iso']], $lanuage);
        }
    }
}
