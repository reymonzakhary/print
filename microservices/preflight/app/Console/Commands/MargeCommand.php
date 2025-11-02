<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class MargeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marge {a} {b} {c}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
//                "A=",
//                $this->argument('a'),
//                "B=",
//                $this->argument('b'),
//                "cat A1 B2 output",
//                $this->argument('c'),
//            ]
//        );
//        $process->run();
//        $this->info( $process->getOutput());

        $cmd = "pdftk A={$this->argument('a')} B={$this->argument('b')} cat A1 B2 output {$this->argument('c')}";
       
        exec($cmd);
    }
}
