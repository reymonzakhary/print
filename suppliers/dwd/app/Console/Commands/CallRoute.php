<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Http\Request;

class CallRoute extends Command {

    protected $name = 'route:call';
    protected $description = 'Call route from CLI';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Calling the route ....');
//        $request = Request::create($this->option('uri'), 'GET');
        $request=app('App\Http\Controllers\ProductPrices\Excludes\ExcludeController')
            ->show($this->option('cat_id'));
        $this->info($request);
    }

    protected function getOptions()
    {
        return [
            ['cat_id', null, InputOption::VALUE_REQUIRED, 'The path of the route to be called', null],
        ];
    }

}
