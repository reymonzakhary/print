<?php

namespace Database\Seeders\Tenants;

use App\Models\Tenant\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
            [
                'name' => 'meter',
                'short_name' => 'm',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'liter',
                'short_name' => 'l',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'kilogram',
                'short_name' => 'kg',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'piece',
                'short_name' => 'pcs',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'pixel',
                'short_name' => 'px',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'pound',
                'short_name' => 'lb', // 1 pound (lb) is equal to 0.45359237 kilograms (kg).  10 lb × 0.45359237
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'gram',
                'short_name' => 'g',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'inch',
                'short_name' => 'in',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'centimeter',
                'short_name' => 'cm',
                'operator' => 'multiply',
                'operator_value' => 1
            ],
            [
                'name' => 'millimeter',
                'short_name' => 'mm',
                'operator' => 'multiply',
                'operator_value' => 1
            ]
        ];

        collect($units)->map(fn($unit) => Unit::firstOrCreate($unit));
    }
}
