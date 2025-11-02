<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ConverterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'converter {signature} {output}';

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
//                "convert",
//                $this->argument('signature'),
//                " -transparent white -page  a4+20+172 -quality 1200 ",
//                $this->argument('output')
//            ]
//        );
//        $process->run();
//        $this->info( $process->getOutput());
        $cmd = "convert {$this->argument('signature')} -transparent white -page a4+20+172 -quality 1200 {$this->argument('output')}";
        exec($cmd);
    }
}
