<?php

namespace Modules\Campaign\Http\Controllers\Campaigns;

use App\Models\Tenant\DesignProviderTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Http\Requests\AttatchTemplateToCampaignRequest;
use Modules\Campaign\Http\Requests\UpdateTemplateCampaignRequest;
use Modules\Campaign\Transformers\CampaignTemplateResource;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index(
        Campaign $campaign
    )
    {
        return CampaignTemplateResource::collection($campaign->providerTemplates()->get());
    }

    /**
     * Store a newly created resource in storage.
     * @param AttatchTemplateToCampaignRequest $request
     * @param Campaign                         $campaign
     * @return JsonResponse
     */
    public function store(
        AttatchTemplateToCampaignRequest $request,
        Campaign                         $campaign
    )
    {
        if ($campaign->providerTemplates()->syncWithoutDetaching([
            $request->provider_template_id => [
                'assets' => $request->get('assets')
            ]
        ])) {
            return response()->json([
                'message' => __('Template has been attached successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We could\'n handle your request.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateTemplateCampaignRequest $request
     * @param Campaign                      $campaign
     * @return JsonResponse
     */
    public function update(
        UpdateTemplateCampaignRequest $request,
        Campaign                      $campaign
    )
    {
        if ($campaign->providerTemplates()->updateExistingPivot($request->provider_template_id, [
            'assets' => $request->get('assets')
        ])) {
            return response()->json([
                'message' => __('Template has been updated successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We could\'n handle your request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     * @param Campaign               $campaign
     * @param DesignProviderTemplate $designProviderTemplate
     * @return JsonResponse
     */
    public function destroy(
        Campaign $campaign,
        int      $designProviderTemplate
    )
    {
        if ($campaign->providerTemplates()->detach($designProviderTemplate)) {
            return response()->json([
                'message' => __('Template has been detached successfully'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We could\'n handle your request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

}
