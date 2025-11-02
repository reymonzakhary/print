<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Margins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\UpdateCategoryMarginRequest;
use App\Services\Margins\MarginService;
use GuzzleHttp\Exception\GuzzleException;
use Hyn\Tenancy\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarginController extends Controller
{
    /**
     * @var MarginService
     */
    protected MarginService $marginService;

    protected ?Website $tenant;

    /**
     * MarginController constructor.
     * @param Request       $request
     * @param MarginService $marginService
     */
    public function __construct(
        Request       $request,
        MarginService $marginService
    )
    {
        $this->tenant = $request->tenant;
        $this->marginService = $marginService;
    }

    /**
     * @param Request $request
     * @param string  $category
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function show(
        Request $request,
        string  $category
    )
    {
        $proxy = $this->marginService->obtainCategoryMargin(
            $this->tenant->uuid,
            $category
        );

        return response()->json([
            'data' => $proxy,
            'message' => null,
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param UpdateCategoryMarginRequest $request
     * @param string                      $category
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function update(
        UpdateCategoryMarginRequest $request,
        string                      $category
    )
    {
        $proxy = $this->marginService->obtainUpdatedCategoryMargin(
            $this->tenant->uuid,
            $category,
            $request->validated()
        );

        if(optional($proxy)['status'] !== 200){
            return response()->json([
                "message" => optional($proxy)['message'],
                "status" => optional($proxy)['status'],
                "data" => []
            ]);
        }

        return response()->json([
            'message' => optional($proxy)['message'],
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }
}
