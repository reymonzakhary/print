<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class SeparatePdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:separate {pages} {input} {output} {split?}';

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
        $pages = explode('~', $this->argument('pages'));
        if ($this->argument('split') === 'split') {
            if (!file_exists($this->argument('output')) && !mkdir($concurrentDirectory = $this->argument('output'), 0777, true) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $cmd = 'pdftk ' . $this->argument('input') . ' burst output ' . $this->argument('output') . DIRECTORY_SEPARATOR . '%d.pdf';
        } else {
            $cmd = 'pdftk ' . $this->argument('input') . ' cat ' . implode(' ', $pages) . ' output ' . $this->argument('output') . '.pdf';
        }
        shell_exec($cmd);
    }
}
