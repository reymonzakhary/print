<?php

namespace App\Console\Commands\Modules;

use App\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenancyMigrateRollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:tenancy:migrate:rollback { module? } { website_id? }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenancy migrate rollback with the selected modules';

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
        $module_name = $this->argument('module');
        $website_id = $this->argument('website_id');

        if ($module_name) {
            $module = Module::where('name', $module_name)->first();
            if ($website_id) {
                Artisan::call('tenancy:migrate:rollback --website_id=' . $website_id . ' --path=' . $module->path . '/Database/Migrations');
            } else {
                foreach ($module->hostnames as $hostname) {
                    Artisan::call('tenancy:migrate:rollback --website_id=' . $hostname->website_id . ' --path=' . $module->path . '/Database/Migrations');
                }
            }
        } else {
            $modules = Module::all();
            foreach ($modules as $module) {
                if ($website_id) {
                    Artisan::call('tenancy:migrate:rollback --website_id=' . $website_id . ' --path=' . $module->path . '/Database/Migrations');
                } else {
                    foreach ($module->hostnames as $hostname) {
                        Artisan::call('tenancy:migrate:rollback --website_id=' . $hostname->website_id . ' --path=' . $module->path . '/Database/Migrations');
                    }
                }
            }
        }
        $this->info('migration roll back with success');
    }
}
