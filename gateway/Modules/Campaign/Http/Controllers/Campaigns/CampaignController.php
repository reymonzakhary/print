<?php

namespace Modules\Campaign\Http\Controllers\Campaigns;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Events\CreateCampaignFolderEvent;
use Modules\Campaign\Http\Requests\StoreCampaignRequest;
use Modules\Campaign\Http\Requests\UpdateCampaignRequest;
use Modules\Campaign\Transformers\CampaignResource;


class CampaignController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {

        return CampaignResource::collection(
            Campaign::get()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK,
        ]);
    }

    /**
     * @param StoreCampaignRequest $request
     * @return CampaignResource
     */
    public function store(
        StoreCampaignRequest $request
    )
    {
        $campaign = Campaign::create($request->validated());
        if ($request->file('file')) {
            event(new FilesUploading($request));
            $campaign->addMedia(
                $request->file('file'),
                "campaigns/{$campaign->slug}",
                $request->input('overwrite'),
                $request->input('originalPath') . "campaigns/{$campaign->slug}",
                'campaigns'
            );
            event(new FilesUploaded($request));
        }
        event(new CreateCampaignFolderEvent($campaign, tenant()->uuid));
        return CampaignResource::make(
            $campaign
        )->additional([
            'message' => __('Campaign has been created successfully'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * @param Campaign $campaign
     * @return CampaignResource
     */
    public function show(
        Campaign $campaign
    )
    {
        return CampaignResource::make($campaign)
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * @param UpdateCampaignRequest $request
     * @param Campaign              $campaign
     * @return JsonResponse
     */
    public function update(
        UpdateCampaignRequest $request,
        Campaign              $campaign
    )
    {
        if ($campaign->update($request->validated())) {
            if ($request->get('file')) {
                // @todo
                // remove old media from storage and db
                // then upload the new one
                event(new FilesUploading($request));
                $campaign->addMedia(
                    $request->file('file'),
                    "campaigns/{$campaign->slug}",
                    $request->input('overwrite'),
                    $request->input('originalPath') . "campaigns/{$campaign->slug}",
                    'campaigns'
                );
                event(new FilesUploaded($request));
            }

            return response()->json([
                'message' => __('Campaign has been updated successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        /**
         * error handling
         */
        return response()->json([
            'message' => __('We could\'n update the campaign data, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Campaign $campaign
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(
        Campaign $campaign
    )
    {
        Storage::disk('tenant')->deleteDirectory("campaigns/{$campaign->slug}");
        $campaign->exports->map(function ($export) {
            $export->removeMedia('campaign-partner-export');
            $export->delete();
        });
        if ($campaign->delete()) {
            return response()->json([
                'message' => __('Campaign has been deleted successfully.'),
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
