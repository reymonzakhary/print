<?php

namespace App\Http\Controllers\System\V2\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Http\Resources\Options\MatchedOptionResource;
use App\Http\Resources\Options\UnmatchedOptionResource;
use App\Services\System\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class MatchedOptionController extends Controller
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
    public function index()
    {
        return MatchedOptionResource::collection(
            $this->optionService->obtainMatchedOptions()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
