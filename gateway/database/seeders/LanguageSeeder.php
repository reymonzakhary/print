<?php

namespace Database\Seeders;

use App\Models\Language;
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

        $languages = [
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
        foreach ($languages as $language) {
            Language::firstOrCreate(['iso' => $language['iso']], $language);
        }
    }
}
