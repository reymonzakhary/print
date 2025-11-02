<?php

namespace Database\Seeders\Tenants;

use App\Models\Tenants\Context;
use Illuminate\Database\Seeder;

class ContextSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $context = [
            [
                'name' => 'mgr',
                'config' => [
                    'culture_key' => 'en',
                    'languages' => ['nl', 'en']
                ]
            ],
            [
                'name' => 'web',
                'config' => [
                    'culture_key' => 'en',
                    'languages' => ['nl', 'en']
                ]
            ]
        ];

        collect($context)->each(fn($ctx) => Context::firstOrCreate(['name' => $ctx['name']], $ctx));
    }
}
