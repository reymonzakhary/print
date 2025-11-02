<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AddLayerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:layer {background} {input} {output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '{background} {input} {output}';

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
//        $process = new Process(
//            [
//                "pdftk",
//                $this->argument('background') ,
//                " multibackground " ,
//                $this->argument('input') ,
//                " output " ,
//                $this->argument('output')
//            ]
//        );
//        $process->run();
//        $this->info( $process->getOutput());
        $cmd = "pdftk {$this->argument('background') } multibackground {$this->argument('input')} output {$this->argument('output')}";
        exec($cmd);
    }
}
