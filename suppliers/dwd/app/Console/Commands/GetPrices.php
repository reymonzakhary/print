<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;

class GetPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
    */
    protected $signature = 'category:prices 
                            {category_id : The ID of the category you desire to scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets Products and there prices of given ID';

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
        $this->info('Calling the Product Prices...');

        app('App\Http\Controllers\ProductPrices\ProductPriceController')->store($this->argument('category_id'));

        $this->info('Products and there prices are inserted to DB...');

        // if ($this->confirm('Do you wish to run the Queue Listener?')) {
        //     Artisan::call('queue:listen');
        // }
    }
}