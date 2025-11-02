<?php

namespace App\Http\Controllers\System\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Http\Requests\Options\AttachOptionRequest;
use App\Http\Requests\Options\StoreSystemOptionRequest;
use App\Services\System\Options\OptionService;
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
     * OptionController constructor.
     * @param OptionService $optionService
     */
    public function __construct(
        OptionService $optionService
    )
    {
//        $this->middleware('auth:web');
        $this->optionService = $optionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|string
     * @throws GuzzleException
     */
    public function index(Request $request)
    {
        return $this->optionService->obtainSystemOptions([
            'per_page' => $request->input('per_page', 10),
            'page' => $request->input('page', 1),
            'filter' => $request->input('filter')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSystemOptionRequest $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function store(
        StoreSystemOptionRequest $request
    )
    {
        return $this->optionService->storeSystemOptions($request->validated());
    }

    /**
     * Display the specified resource.
     *
     * @param string $option
     * @return Response|string
     * @throws GuzzleException
     */
    public function show(
        string $option
    )
    {
        return $this->optionService->obtainSystemOption($option);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreSystemOptionRequest $request
     * @param string                   $option
     * @return Response|string
     * @throws GuzzleException
     */
    public function update(
        StoreSystemOptionRequest $request,
        string                   $option
    )
    {
        return $this->optionService->updateSystemOptions($option, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $option
     * @return Response|string
     * @throws GuzzleException
     */
    public function destroy(
        string $option
    )
    {
        return $this->optionService->deleteSystemOption($option);
    }

    /**
     * @param AttachOptionRequest $request
     * @param string              $category
     * @param string              $box
     * @param string              $option
     * @return string
     * @throws GuzzleException
     */
    public function attach(
        AttachOptionRequest $request,
        string              $option
    )
    {
        return $this->optionService->obtainAttachSystemOptions($option, $request->validated());
    }
}
