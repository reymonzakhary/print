<?php

namespace App\Http\Controllers\System\V2\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Http\Requests\Options\MergeStoreRequest;
use App\Services\System\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class MergeOptionController extends Controller
{
    /**
     * @var OptionService
     */
    protected OptionService $optionService;

    /**
     * MergeCategoryController constructor.
     * @param OptionService $optionService
     */
    public function __construct(
        OptionService $optionService
    )
    {
//        $this->middleware('auth');
        $this->optionService = $optionService;
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
        $proxy = $this->optionService->mergeSystemOptions($request->validated(), [
            'new' => $request->get('new')
        ]);

        if(!in_array(optional($proxy)['status'], [201,200])) {
            throw ValidationException::withMessages([
                'options' => optional($proxy)['message'] ?? __('Something went wrong')
            ]);
        }
        return $proxy;
    }
}
