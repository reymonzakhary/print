<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;

class UpdateServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update assortments, boxes, options and boops in boops service';

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
        /**
         * update assortments
         */
        $this->info(app('App\Http\Controllers\Categories\CategoryController')->storeBoopService());

        /**
         * update boxes
         */
        $this->info(app('App\Http\Controllers\Boxes\BoxController')->storeBoxBoopService());

        /**
         * update options
         */
        $this->info(app('App\Http\Controllers\Boxes\BoxController')->storeOptionBoopService());

        /**
         * update boops
         */
        $this->info(app('App\Http\Controllers\Boops\BoopController')->storeBoopService());

        /**
         * create products
         */
        $this->info(app('App\Http\Controllers\Products\ProductController')->store());

        /**
         * update price service
         */
//        $this->info(app('App\Http\Controllers\BoopPrices\BoopPriceController')->storePriceService());
    }
}
