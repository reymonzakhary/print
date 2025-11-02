<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ZipFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zip:dir {input} {output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zip directory';

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
        shell_exec("/usr/bin/sh /var/www/zipAction.sh {$this->argument('output')}  {$this->argument('input')}");
    }
}
