<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fresh Install';

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
     * @return int
     */
    public function handle()
    {
        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->info(Artisan::output());

        Artisan::call('tenancy:migrate:fresh', ['--force' => true]);
        $this->info(Artisan::output());

        Artisan::call('db:seed', ['--force' => true]);
        $this->info(Artisan::output());

        Artisan::call('tenancy:db:seed', ['--force' => true]);
        $this->info(Artisan::output());

        Artisan::call('passport:install', ['--force' => true]);
        $this->info(Artisan::output());
        return 0;
    }
}
