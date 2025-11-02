<?php

namespace Modules\Campaign\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Campaign\Events\GenerateCampaignEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ExportCampaignCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:campaign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export campaign templates';

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
        Log::info('working before running working');
        collect(DB::table('campaigns')->get())->map(function ($campaign) {
            // check if campaign active for exporting
            if (
                $campaign->active &&
                $campaign->providerTemplates->count() !== 0 &&
                collect($campaign->providerTemplates)->first()->pivot->assets &&
                !collect($campaign->exports)->first()->finished
            ) {
                Log::info('working on running working');
                event(new GenerateCampaignEvent($campaign, request()->tenant->uuid, request()->tenant->fqdn, request()->user()));
            }

            Log::info('not working');
        });
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
