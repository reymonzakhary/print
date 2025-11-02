<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddLayerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:layer {background} {input} {output} {type} {page?} {time?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '{background} {input} {output} {type} {page?}';

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

        /**
         * multistamp
         */
        $type = ($this->argument('type') === 'stamp') ? 'multistamp' : 'multibackground';

        if ($this->argument('page')) {
            $time = $this->argument('time') . microtime(true);
            shell_exec("/usr/bin/sh /var/www/pdftk.sh {$this->argument('input')} {$this->argument('background') } {$this->argument('output')} {$type} {$this->argument('page')} {$time}");
        } else {
            exec("pdftk {$this->argument('input')} $type {$this->argument('background') } output {$this->argument('output')}");
        }
    }
}
