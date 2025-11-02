<?php

namespace App\Http\Controllers\System\V2\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boxes\AttachBoxRequest;
use App\Http\Requests\Boxes\StoreSystemBoxRequest;
use App\Http\Resources\Boxes\PrintBoxResource;
use App\Http\Resources\Boxes\SystemBoxResource;
use App\Services\System\Boxes\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

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
        $this->boxService = $boxService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function index(
        Request $request
    )
    {
        return SystemBoxResource::collection($this->boxService->obtainSystemBoxes([
            'per_page' => $request->input('per_page', 10),
            'page' => $request->input('page', 1),
            'filter' => $request->input('filter')
        ]))->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSystemBoxRequest $request
     * @return SystemBoxResource
     * @throws GuzzleException
     */
    public function store(
        StoreSystemBoxRequest $request
    ): SystemBoxResource
    {
        return SystemBoxResource::make($this->boxService->storeSystemBoxes($request->validated()))
            ->additional([
                'message' => null,
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param string $box
     * @return PrintBoxResource
     * @throws GuzzleException
     */
    public function show(
        string $box
    ): PrintBoxResource
    {
        return PrintBoxResource::make($this->boxService->obtainSystemBox($box))
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreSystemBoxRequest $request
     * @param string                $box
     * @return string
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
     * @param Request $request
     * @param string $box
     * @return string
     * @throws GuzzleException
     */
    public function destroy(
        Request $request,
        string $box
    )
    {
        return $this->boxService->deleteSystemBox($box, ["force" => $request->input('force')]);
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

    /**
     * Detach a box from the system.
     *
     * @param AttachBoxRequest $request The request containing the box to detach
     * @param string $box The box to detach
     * @return string The response of detaching the box from the system
     * @throws GuzzleException
     */
    public function detach(
        AttachBoxRequest $request,
        string           $box
    )
    {
        return $this->boxService->obtainDetachSystemBoxes($box, $request->validated());
    }
}
