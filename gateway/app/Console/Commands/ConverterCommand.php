<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConverterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:converter {signature} {output} {x} {y}';

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
//        exec("convert signature-image -resize 26% -transparent white -page a4+25+102 -quality 75 outputs/stamp.pdf");
//        exec("convert xc:none -page A4 outputs/blank1.pdf");
//        exec("convert xc:none -page A4 outputs/blank2.pdf");
//        exec("convert xc:none -page A4 outputs/blank3.pdf");
//        exec("pdftk outputs/blank1.pdf outputs/stamp.pdf outputs/blank2.pdf  outputs/blank3.pdf cat output outputs/sign.pdf");
//        exec("pdftk main.pdf multistamp outputs/sign.pdf output outputs/final.pdf");
//        convert fakeSignature_1.pdf -resize 26% -transparent white -page a4+25+102 -quality 75 stamp.pdf

        $cmd = "convert {$this->argument('signature')} -resize 100% -transparent white -page a4+{$this->argument('x')}+{$this->argument('y')} -quality 1200 {$this->argument('output')}";
        exec($cmd);
    }
}
