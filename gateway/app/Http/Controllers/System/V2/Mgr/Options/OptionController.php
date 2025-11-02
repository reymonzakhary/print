<?php

namespace App\Http\Controllers\System\V2\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Http\Requests\Options\AttachOptionRequest;
use App\Http\Requests\Options\StoreSystemOptionRequest;
use App\Http\Resources\Options\SystemOptionResource;
use App\Services\System\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

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
        $this->optionService = $optionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function index(Request $request)
    {
        return SystemOptionResource::collection($this->optionService->obtainSystemOptions([
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
     * @param StoreSystemOptionRequest $request
     * @return SystemOptionResource
     * @throws GuzzleException
     */
    public function store(
        StoreSystemOptionRequest $request
    )
    {
        return SystemOptionResource::make(
            $this->optionService->storeSystemOptions($request->validated())
        )
            ->additional([
                'message' => __('Option created successfully.'),
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param string $option
     * @return SystemOptionResource
     * @throws GuzzleException
     */
    public function show(
        string $option
    )
    {
        return SystemOptionResource::make(
            $this->optionService->obtainSystemOption($option)
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);

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
     * @param Request $request
     * @return string
     * @throws GuzzleException
     */
    public function destroy(
        string $option,
        Request $request
    )
    {
        return $this->optionService->deleteSystemOption($option,  ["force" => $request->input('force')]);
    }

    /**
     * Attach the specified option to the system.
     *
     * @param AttachOptionRequest $request The request containing the option to attach
     * @param string $option The option to attach
     * @return mixed The result of attaching the option to the system
     * @throws GuzzleException
     */
    public function attach(
        AttachOptionRequest $request,
        string              $option
    )
    {
        return $this->optionService->obtainAttachSystemOptions($option, $request->validated());
    }

    /**
     * Detach the specified option from the system.
     *
     * @param AttachOptionRequest $request The request containing the option to detach
     * @param string $option The option to detach
     * @return mixed The result of detaching the option from the system
     * @throws GuzzleException
     */
    public function detach(
        AttachOptionRequest $request,
        string              $option
    )
    {
        return $this->optionService->obtainDetachSystemOptions($option, $request->validated());
    }
}
