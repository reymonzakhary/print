<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CategoryExcludes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
    */
    protected $signature = 'category:excludes
                            {category_id : The ID of the category you desire to scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans the excludes of a given category, you must run it after having all prices';

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
        $this->info('Calculating the Excluded options...');

        $res = app('App\Http\Controllers\ProductPrices\Excludes\ExcludeController')->show($this->argument('category_id'));

        echo $this->info($res);

    }
}
