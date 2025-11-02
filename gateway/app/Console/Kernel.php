<?php

namespace App\Console;

use App\Jobs\RestartSupervisorJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\Campaign\Jobs\DeleteFilesJob;
use Modules\Campaign\Jobs\GenerateExportJob;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new DeleteFilesJob)->daily();
        $schedule->job(new GenerateExportJob)->daily();
//        $schedule->job(new RestartSupervisorJob())->everyMinute();

        $schedule->command('optimize:clear')->hourly();
        $schedule->command('websockets:clean')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        $this->load(__DIR__ . '/Commands/Modules');

        require base_path('routes/console.php');
    }
}
