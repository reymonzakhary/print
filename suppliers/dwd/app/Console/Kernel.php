<?php

namespace App\Console;

use App\Console\Commands\CallRoute;
use App\Console\Commands\GetPrices;
use App\Console\Commands\RetryFailed;
use App\Console\Commands\ScanCategoryToV3;
use App\Console\Commands\UpdateServices;
use App\Console\Commands\CategoryExcludes;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ScanCategoryToV3::class,
        GetPrices::class,
        CategoryExcludes::class,
        RetryFailed::class,
        CallRoute::class,
        UpdateServices::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
