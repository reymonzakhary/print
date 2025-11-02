<?php

namespace App\Http\Controllers\System\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boxes\MergeStoreRequest;
use App\Services\System\Boxes\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;

class MergeBoxController extends Controller
{
    /**
     * @var BoxService
     */
    protected BoxService $boxService;

    /**
     * MergeCategoryController constructor.
     * @param BoxService $boxService
     */
    public function __construct(
        BoxService $boxService
    )
    {
//        $this->middleware('auth');
        $this->boxService = $boxService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MergeStoreRequest $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function store(
        MergeStoreRequest $request
    )
    {
        return $this->boxService->mergeSystemBoxes($request->validated(), [
            'new' => $request->get('new')
        ]);
    }
}
