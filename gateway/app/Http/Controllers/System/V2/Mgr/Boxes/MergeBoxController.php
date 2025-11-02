<?php

namespace App\Http\Controllers\System\V2\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boxes\MergeStoreRequest;
use App\Services\System\Boxes\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

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
        $proxy = $this->boxService->mergeSystemBoxes($request->validated(), [
            'new' => $request->get('new')
        ]);

        if(!in_array(optional($proxy)['status'], [200,201])) {
            throw ValidationException::withMessages([
                'boxes' => optional($proxy)['message'] ?? __('Something went wrong')
            ]);
        }
        return $proxy;
    }
}
