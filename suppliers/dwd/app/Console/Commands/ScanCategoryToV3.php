<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;

class ScanCategoryToV3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
    */
    protected $signature = 'category:scan
                            {category_id : The ID of the category you desire to scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets Category, Properties, Options, and create Boops of given ID';

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
        $this->info('Refreshing the DB...');

//       Artisan::call('migrate:fresh');
//
//        $this->info('DB Refresh...');
//
//        $this->info('DB Migrated...');
//
//        $this->info('Calling Categories...');
//
//       app('App\Http\Controllers\Categories\CategoryController')->store();
//
//        $this->info('Categories stored successfully to DB...');
//
//        $this->info('Calling Category\'s Properties and Options...');
//
//        app('App\Http\Controllers\Boxes\BoxController')->store($this->argument('category_id'));
//
//        $this->info('Properties and Options stored successfully to DB...');
//
//        $this->info('Creating BOOPS Minifest...');

        app('App\Http\Controllers\ProductPrices\ProductPriceController')->store($this->argument('category_id'));
        //app('App\Http\Controllers\Boops\BoopController')->store($this->argument('category_id'));

        $this->info('Inserting products and prices to db ...');
//        $this->info('BOOPS Minifest Stored Safely to DB...');

    }
}
