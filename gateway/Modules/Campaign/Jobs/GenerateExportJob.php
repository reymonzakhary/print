<?php

namespace Modules\Campaign\Jobs;

use Carbon\Carbon;
use Hyn\Tenancy\Environment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Http\Controllers\Campaigns\CampaignExportController;

class GenerateExportJob implements ShouldQueue
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
            Campaign::where('config->export_deleted', '!=', true)
                ->whereNull('config->export')
                ->whereDate('start_on', Carbon::now()->format('Y-m-d'))
                ->get()
                ->map(function ($campaign) use ($tenant) {
                    $request = new Request();
                    $request->tenant = tenant();
                    $request->domain = $tenant->fqdn;
                    $res = app(CampaignExportController::class)->store($request, $campaign);
                });
        });
    }
}
