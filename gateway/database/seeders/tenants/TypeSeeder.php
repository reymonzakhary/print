<?php

namespace Database\Seeders\Tenants;

use App\Models\Tenants\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'name' => 'web',
                'slug' => 'web',
                'action' => true,
                'ns' => 'web'
            ],
            [
                'name' => 'mgr',
                'slug' => 'mgr',
                'action' => true,
                'ns' => 'mgr'
            ],
            [
                'name' => 'system',
                'slug' => 'system',
                'action' => true,
                'ns' => 'system'
            ]
        ];
        collect($types)->map(fn($type) => Type::firstOrCreate($type));
    }
}
