<?php

namespace App\Http\Controllers\System\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boxes\AttachBoxRequest;
use App\Http\Requests\Boxes\StoreSystemBoxRequest;
use App\Services\System\Boxes\BoxService;
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
     * BoxController constructor.
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
     * @param Request $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function index(
        Request $request
    )
    {
        return $this->boxService->obtainSystemBoxes([
            'per_page' => $request->input('per_page', 10),
            'page' => $request->input('page', 1),
            'filter' => $request->input('filter')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSystemBoxRequest $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function store(
        StoreSystemBoxRequest $request
    )
    {
        return $this->boxService->storeSystemBoxes($request->validated());
    }

    /**
     * Display the specified resource.
     *
     * @param string $box
     * @return Response|string
     * @throws GuzzleException
     */
    public function show(
        string $box
    )
    {
        return $this->boxService->obtainSystemBox($box);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreSystemBoxRequest $request
     * @param string                $box
     * @return Response|string
     * @throws GuzzleException
     */
    public function update(
        StoreSystemBoxRequest $request,
        string                $box
    )
    {
        return $this->boxService->updateSystemBoxes($box, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $box
     * @return Response|string
     * @throws GuzzleException
     */
    public function destroy(
        string $box
    )
    {
        return $this->boxService->deleteSystemBox($box);
    }

    /**
     * @param AttachBoxRequest $request
     * @param string           $box
     * @return string
     * @throws GuzzleException
     */
    public function attach(
        AttachBoxRequest $request,
        string           $box
    )
    {
        return $this->boxService->obtainAttachSystemBoxes($box, $request->validated());
    }
}
