<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RetryFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
    */
    protected $signature = 'retry:failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry Failed Jobs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {           
        $jobs = DB::table('failed_jobs')->get();

        foreach( $jobs as $job){
            Artisan::call('queue:retry ' . $job->id);
            $this->info($job->id);
        }

    }
}