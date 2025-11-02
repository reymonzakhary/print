<?php

namespace App\Http\Controllers\System\V2\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Boxes\SystemUnmatchedBoxResource;
use App\Services\System\Boxes\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class UnmatchedBoxController extends Controller
{

    /**
     * @var BoxService
     */
    protected BoxService $boxService;

    /**
     * BoxController constructor.
     * @param BoxService $boxService
     */
    public function __construct(
        BoxService $boxService
    )
    {
        $this->boxService = $boxService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function index(): AnonymousResourceCollection
    {
        return SystemUnmatchedBoxResource::collection(
            $this->boxService->obtainUnmatchedSystemBoxes()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Destroy a box record from the system.
     *
     * @param string $box The box identifier to be deleted.
     * @return JsonResponse JSON response containing delete status information.
     * @throws GuzzleException
     */
    public function destroy(
        string $box
    ): JsonResponse
    {
        $proxy = $this->boxService->deleteUnmatchedSystemBoxes($box);
        if(optional($proxy)['status'] === 200) {
            return response()->json([
                'message' => $proxy['message'],
                'status' => $proxy['status']
            ]);
        }

        return response()->json([
            'message' => optional($proxy)['message']??
                __('We couldn\'t delete box :box from the system', ['box' => $box]),
            'status' => optional($proxy)['status']??Response::HTTP_UNPROCESSABLE_ENTITY
        ], optional($proxy)['status']??Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
