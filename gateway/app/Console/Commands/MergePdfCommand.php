<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MergePdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:merge {inputs} {output}';

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

        $files = Str::replace('~', ' ', $this->argument('inputs'));
//        $process = new Process(['pdftk', $files.' cat output '.$this->argument('output')]);
//        try {
//            $process->mustRun();
//            echo  $process->getOutput();
//        } catch (ProcessFailedException $exception) {
//            echo $exception->getMessage();
//        }
//        $res = shell_exec('pdftk ' . $files . ' cat output ' . $this->argument('output'));
        $res = shell_exec("gs -q -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile={$this->argument('output')} {$files}");
    }
}
