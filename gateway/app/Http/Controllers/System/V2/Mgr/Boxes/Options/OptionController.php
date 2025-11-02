<?php

namespace App\Http\Controllers\System\V2\Mgr\Boxes\Options;

use App\Http\Controllers\Controller;
use App\Services\System\Boxes\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OptionController extends Controller
{

    /**
     * @var OptionService
     */
    protected OptionService $optionService;

    /**
     * Create a new controller instance.
     *
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
     * Display a listing of the resource.
     *
     * @param string $category
     * @param string $box
     * @return Response|string
     * @throws GuzzleException
     */
    public function index(
        Request $request,
        string  $box
    )
    {
        return $this->optionService->obtainOptionsByBox($box, [
            'per_page' => $request->input('per_page', 10),
            'page' => $request->input('page', 1),
            'filter' => $request->input('filter')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $category
     * @param string $box
     * @param string $option
     * @return Response|string
     * @throws GuzzleException
     */
    public function show(
        string $box,
        string $option
    )
    {
        return $this->optionService->obtainOptionByBox($box, $option);
    }
}
