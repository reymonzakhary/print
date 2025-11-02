<?php

namespace App\Http\Controllers\System\V2\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Boxes\SystemBoxResource;
use App\Services\System\Boxes\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnlinkedBoxController extends Controller
{
    /**
     * @var BoxService
     */
    protected BoxService $BoxService;

    /**
     * UnlinkedCategory constructor.
     * @param BoxService $BoxService
     */
    public function __construct(
        BoxService $BoxService
    )
    {
        $this->BoxService = $BoxService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return string
     * @throws GuzzleException
     */
    public function index(
        Request $request
    )
    {
        return SystemBoxResource::collection($this->BoxService->obtainUnlinkedBoxes([
            "page" => $request->get("page"),
            "per_page" => $request->get("per_page"),
            "filter" => $request->get("filter"),
        ]))
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

}
