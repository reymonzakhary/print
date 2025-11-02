<?php

namespace Database\Seeders\Tenants;

use App\Enums\DesignProviderType;
use App\Models\Tenants\DesignProvider;
use Illuminate\Database\Seeder;

class DesignProvidersSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $providers = [
            [
                'name' => 'Prindustry design tool',
                'type' => DesignProviderType::PRINDUSTRY->value,
            ],
            [
                'name' => 'Conneo preflight',
                'type' => DesignProviderType::CONNEO->value,
            ]
        ];

        collect($providers)->each(fn($provider) => DesignProvider::updateOrCreate(['name' => $provider['name']], $provider));
    }
}
