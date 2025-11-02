<?php

namespace App\Http\Controllers\System\V2\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Http\Resources\Options\UnmatchedOptionResource;
use App\Services\System\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class UnmatchedOptionController extends Controller
{

    /**
     * OptionController constructor.
     * @param OptionService $optionService
     */
    public function __construct(
        protected OptionService $optionService
    ){}

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function index(): AnonymousResourceCollection
    {
        return UnmatchedOptionResource::collection(
            $this->optionService->obtainUnmatchedSystemOptions()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }


    /**
     * Deletes the specified option from the system.
     *
     * @param string $option
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function destroy(
        string $option
    ): JsonResponse
    {
        $proxy = $this->optionService->deleteUnmatchedSystemOptions($option);
        if(optional($proxy)['status'] === 200) {
            return response()->json([
                'message' => $proxy['message'],
                'status' => $proxy['status']
            ]);
        }

        return response()->json([
            'message' => optional($proxy)['message']??
                __('We couldn\'t delete option :option from the system', ['options' => $option]),
            'status' => optional($proxy)['status']??Response::HTTP_UNPROCESSABLE_ENTITY
        ], optional($proxy)['status']??Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
