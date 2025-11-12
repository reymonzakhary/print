<?php

namespace Database\Seeders\tenants;

use App\Events\PrintingMethods\CreatePrintingMethodEvent;
use App\Models\Tenant\PrintingMethod;
use Illuminate\Database\Seeder;

class PrintingMethods extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pm = [
            [
                "name" => "Digital",
                "iso" => "en",
            ],[
                "name" => "Offset",
                "iso" => "en",
            ],[
                "name" => "Inkjet",
                "iso" => "en",
            ],[
                "name" => "All",
                "iso" => "en",
            ]
        ];
        collect($pm)->each(function ($pm) {
            $method = PrintingMethod::query()->updateOrCreate($pm);
            event(new CreatePrintingMethodEvent($method, 'en'));
        });
    }
}
