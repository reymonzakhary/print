<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            [
                'name' => 'Cms',
                'path' => 'Modules/Cms',
                'description' => 'cms',
                'keywords' => 'Cms',
                'is_active' => 1,
                'order' => 0,
                'providers' => '',
                'aliases' => '',
                'files' => '',
                'requires' => ''
            ],
            [
                'name' => 'Ecommerce',
                'path' => 'Modules/Ecommerce',
                'description' => 'Ecommerce',
                'keywords' => 'Ecommerce',
                'is_active' => 1,
                'order' => 0,
                'providers' => '',
                'aliases' => '',
                'files' => '',
                'requires' => ''
            ],
            [
                'name' => 'Campaigns',
                'path' => 'Modules/Campaign',
                'description' => 'Campaign Tool ',
                'keywords' => 'Campaigns',
                'is_active' => 1,
                'order' => 0,
                'providers' => '',
                'aliases' => '',
                'files' => '',
                'requires' => ''
            ]

        ];
        collect($modules)->map(fn($module) => Module::firstOrCreate($module));

    }
}
