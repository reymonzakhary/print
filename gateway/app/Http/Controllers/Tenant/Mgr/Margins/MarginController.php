<?php

namespace App\Http\Controllers\Tenant\Mgr\Margins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Margin\MarginUpdateRequest;
use App\Http\Resources\Margin\MarginResource;
use App\Services\Margins\MarginService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class MarginController extends Controller
{
    /**
     * @var MarginService
     */
    protected MarginService $marginService;

    /**
     * MarginController constructor.
     * @param MarginService $marginService
     */
    public function __construct(
        MarginService $marginService
    )
    {
        $this->marginService = $marginService;
    }

    /**
     *
     * @return AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function index()
    {
        $proxy = $this->marginService->obtainMargin(
            tenant()->uuid
        );

        /** @TODO check the margin service of the response of data. */
        return MarginResource::collection($proxy??[]);
    }

    /**
     * Update the margin.
     *
     * @param MarginUpdateRequest $request The request object containing the margin data.
     * @return JsonResponse The JSON response after updating the margin.
     * @throws GuzzleException
     */
    public function update(
        MarginUpdateRequest $request
    )
    {
        $proxy = $this->marginService->obtainUpdateGeneralMargin(
            $request->tenant->uuid,
            ['general' => $request->get('general')]
        );
        if($proxy['status'] !== 200) {
            return response()->json([
                'message' => $proxy['message'],
                'status' => $proxy['status'],
            ], $proxy['status']);
        }

        return response()->json([
            'message' => __('Margins updated successfully.'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

}
