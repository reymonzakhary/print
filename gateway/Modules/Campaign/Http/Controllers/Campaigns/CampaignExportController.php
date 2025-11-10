<?php

namespace Modules\Campaign\Http\Controllers\Campaigns;

use App\Models\Tenant\User;
use App\Services\Tenant\FM\FileManagerService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignExport;
use Modules\Campaign\Events\GenerateCampaignEvent;

class CampaignExportController extends Controller
{

    /**
     * @param Request  $request
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function store(
        Request  $request,
        Campaign $campaign
    )
    {
        // check if campaign active for exporting
        if (!$campaign->active) {
            return response()->json([
                'message' => __('Please Activate your campaign before continuing with export.'),
                'status' => Response::HTTP_NOT_ACCEPTABLE
            ], Response::HTTP_NOT_ACCEPTABLE);
        }
        // check if campaign has a template relations
        if ($campaign->providerTemplates->count() === 0) {
            return response()->json([
                'message' => __('Please add a template first to your campaign.'),
                'status' => Response::HTTP_NOT_ACCEPTABLE
            ], Response::HTTP_NOT_ACCEPTABLE);
        }
        // check if partner assets exists
        if (!collect($campaign->providerTemplates)->first()->pivot->assets) {
            return response()->json([
                'message' => __('Please add Partner assets before continuing with export.'),
                'status' => Response::HTTP_NOT_ACCEPTABLE
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        // this line gonna send first user in system in case of this method called from GenerateFilesJob
        $user = $request->user() ?? User::first();
        $file = $campaign->file;
        $rows = (new FileManagerService)->readExcel(['path' => tenant()->uuid . "/{$file->path}/{$file->name}"]);

        if (optional($rows)['data']) {
            $comp = collect($rows['data'])->map(fn($row) => $row['Partner'])->toArray();
            $campaign->update([
                'config' => array_merge($campaign->config, ['partner' => $comp])
            ]);

            collect($campaign->providerTemplates)->map(function ($template) use ($request, $user, $rows, $campaign) {
                $time = Carbon::now()->format("Y-m-d H:i:s");
                $export = $campaign->exports()->Create();
                event(new GenerateCampaignEvent($campaign, $request->tenant->uuid, $request->domain, $user, $rows['data'], $time, $export, $template));
            });

        }

        return response()->json([
            'message' => __('Campaign will be generated, and we will notify you later.'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param Campaign $campaign
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(
        Campaign       $campaign,
        CampaignExport $export
    )
    {
        Storage::disk('tenant')->deleteDirectory("campaigns/{$campaign->slug}/output");
        $export->removeMedia('campaign-partner-export');
        if ($export->delete()) {
            if (!$campaign->exports->count()) {
                $config = $campaign->config;
                $config['export_deleted'] = true;
                $config['export'] = 'deleted';
                $campaign->update(['config' => $config]);
            }
            return response()->json([
                'message' => __('Exports has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        /**
         * error handling
         */
        return response()->json([
            'message' => __('We could\'n remove the campaign.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

}
