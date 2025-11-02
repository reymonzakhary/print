<?php

namespace App\Http\Controllers\System\Mgr\Categories\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boxes\ReplaceBoxeToCategoryRequest;
use App\Http\Requests\Boxes\StoreSystemBoxRequest;
use App\Services\System\Categories\Boxes\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BoxController extends Controller
{

    /**
     * @var BoxService
     */
    protected BoxService $boxService;

    /**
     * Create a new controller instance.
     *
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
     * Display a listing of the resource.
     *
     * @param string $category
     * @return Response|string
     * @throws GuzzleException
     */
    public function index(
        Request $request,
        string  $category
    )
    {
        return $this->boxService->obtainBoxesByCategory($category);
    }

    /**
     * @param StoreSystemBoxRequest $request
     * @param string                $category
     * @param string                $box
     * @return string
     * @throws GuzzleException
     */
    public function show(
        string $category,
        string $box
    )
    {
        return $this->boxService->obtainBoxesByCategorySlug($category, $box);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param string                $category
     * @param StoreSystemBoxRequest $request
     * @return string|void
     * @throws GuzzleException
     */
    public function store(
        StoreSystemBoxRequest $request,
        string                $category
    )
    {
        return $this->boxService->storeBoxesByCategorySlug($category, $request->validated());
    }

    /**
     * @param StoreSystemBoxRequest $request
     * @param string                $category
     * @param string                $box
     * @return string
     * @throws GuzzleException
     */
    public function update(
        StoreSystemBoxRequest $request,
        string                $category,
        string                $box
    )
    {
        return $this->boxService->updateBoxesByCategorySlug($category, $box, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $category
     * @param string $box
     * @return string
     * @throws GuzzleException
     */
    public function destroy(
        string $category,
        string $box
    )
    {
        return $this->boxService->deleteBoxesByCategorySlug($category, $box);
    }

    /**
     * @param ReplaceBoxeToCategoryRequest $request
     * @param string                       $category
     * @param string                       $box
     * @return string
     * @throws GuzzleException
     */
    public function attach(
        ReplaceBoxeToCategoryRequest $request,
        string                       $category,
        string                       $box
    )
    {
        return $this->boxService->obtainAttachSystemBoxes($category, $box, $request->validated());
    }
}
