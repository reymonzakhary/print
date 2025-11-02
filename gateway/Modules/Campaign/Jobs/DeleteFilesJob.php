<?php

namespace Modules\Campaign\Jobs;

use Hyn\Tenancy\Environment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Modules\Campaign\Entities\Campaign;

class DeleteFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        collect(tenants())->map(function ($tenant) {
            app(Environment::class)->tenant($tenant->website);
            $time = (string)time();
            Campaign::where('config->outputAvailability', '<', $time)
                ->where('config->export_deleted', '!=', true)
                ->get()
                ->map(function ($campaign) {
                    $config = $campaign->config;
                    $config['export_deleted'] = true;
                    Storage::disk('tenant')->deleteDirectory("campaigns/{$campaign->slug}/output");
                    $campaign->exports->map(function ($export) {
                        $export->removeMedia('campaign-partner-export');
                        $export->delete();
                    });
                    $campaign->update(['config' => $config]);
                });

        });
    }
}
