<?php

namespace App\Http\Controllers\System\V2\Mgr\Categories\Boxes\Options;

use App\Http\Controllers\Controller;
use App\Http\Requests\Options\AttachOptionRequest;
use App\Http\Requests\Options\ReplaceOptionToBoxRequest;
use App\Http\Requests\Options\StoreSystemOptionRequest;
use App\Services\System\Categories\Boxes\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
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
        string $category,
        string $box
    )
    {
        return $this->optionService->obtainOptionsByCategoryAndBox($category, $box);
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
        string $category,
        string $box,
        string $option
    )
    {
        return $this->optionService->obtainOptionByCategoryAndBox($category, $box, $option);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSystemOptionRequest $request
     * @param string                   $category
     * @param string                   $box
     * @return Response|string
     * @throws GuzzleException
     */
    public function store(
        StoreSystemOptionRequest $request,
        string                   $category,
        string                   $box
    )
    {
        return $this->optionService->storeOptionByCategoryAndBox($category, $box, $request->validated());
    }


    /**
     * Update the specified resource in storage.
     *
     * @param StoreSystemOptionRequest $request
     * @param string                   $category
     * @param string                   $box
     * @param string                   $option
     * @return Response|string
     * @throws GuzzleException
     */
    public function update(
        StoreSystemOptionRequest $request,
        string                   $category,
        string                   $box,
        string                   $option
    )
    {
        return $this->optionService->updateOptionByCategoryAndBox($category, $box, $option, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $category
     * @param string $box
     * @param string $option
     * @return Response|string
     * @throws GuzzleException
     */
    public function destroy(
        string $category,
        string $box,
        string $option
    )
    {
        return $this->optionService->deleteOptionByCategoryAndBox($category, $box, $option);
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
        ReplaceOptionToBoxRequest $request,
        string                    $category,
        string                    $box,
        string                    $option
    )
    {
        return $this->optionService->obtainAttachSystemOptions($category, $box, $option, $request->validated());
    }
}
