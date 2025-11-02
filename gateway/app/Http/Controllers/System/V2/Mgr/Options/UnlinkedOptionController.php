<?php

namespace App\Http\Controllers\System\V2\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Http\Resources\Options\SystemOptionResource;
use App\Services\System\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnlinkedOptionController extends Controller
{

    /**
     * UnlinkedCategory constructor.
     * @param OptionService $optionService
     */
    public function __construct(
        protected OptionService $optionService
    ){}

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
        return SystemOptionResource::collection($this->optionService->obtainUnlinkedOptions([
            "page" => $request->get("page"),
            "per_page" => $request->get("per_page"),
            "filter" => $request->get("filter"),
        ]))->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
