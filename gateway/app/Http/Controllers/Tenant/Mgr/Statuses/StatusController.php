<?php

namespace App\Http\Controllers\Tenant\Mgr\Statuses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Status\StoreStatusRequest;
use App\Http\Requests\Status\UpdateStatusRequest;
use App\Http\Resources\Statuses\StatusModelResource;
use App\Http\Resources\Statuses\StatusResource;
use App\Models\Tenants\Status;
use App\Repositories\StatusRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

/**
 * @group Tenant Status
 * Class StatusController
 * @package App\Http\Controllers\Tenant\Mgr\Statuses
 */
class StatusController extends Controller
{

    /**
     * @var array|mixed
     */
    public array $hide = [];

    /**
     * @var StatusRepository
     */
    protected StatusRepository $status;

    /**
     * @param Request $request
     * @param Status  $status
     */
    public function __construct(
        Request $request,
        Status  $status
    )
    {
        $this->hide = $request->get('hide') ?? [];
        $this->status = new StatusRepository($status);

    }

    /**
     * List Statuses
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * "data": [
	 *	{
	 *		"id": 1,
	 *		"code": 300,
	 *		"name": "draft",
	 *		"description": "This item is being created.",
	 *		"created_at": "2024-05-01T11:39:44.000000Z",
	 *		"updated_at": "2024-05-01T11:39:44.000000Z"
	 *	},
     * ],
     * "status": 200,
	 * "message": null,
     * }
     *
     * @response 404
     * {
     * "message":"not found",
     * "status":404
     * }
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {

        /**
         * check if we have status
         */
        return StatusModelResource::collection($this->status->all())
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Show the status
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam status integer required The code of status Example 300.
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 1,
     *        "code": 300,
     *        "name": "draft",
     *        "description": "This item is being created.",
     *        "created_at": "2024-05-01T11:39:44.000000Z",
     *        "updated_at": "2024-05-01T11:39:44.000000Z"
     *    },
     *    "status": 200,
     *    "message": null
     * }
     *
     * @response 404
     * {
     * "message":"not found",
     * "status": 404
     * }
     *
     * @param Status $status
     * @return StatusModelResource
     */
    public function show(
        Status $status
    )
    {
        return StatusModelResource::make($status)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * store new status in the database
     * @param StoreStatusRequest $request
     * @return StatusModelResource
     */
    public function store(
        StoreStatusRequest $request
    )
    {
        return StatusModelResource::make(
            Status::create( $request->validated() )
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * update status
     * @param UpdateStatusRequest $request
     * @param Status $status
     * @return StatusResource
     */
    public function update(
        UpdateStatusRequest $request,
        Status $status
    )
    {
        $status->update( $request->validated() );

        return StatusModelResource::make($status)->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * delete status
     * @param string $status
     * @return JsonResponse
     */
    public function destroy(
        string $status
    )
    {
        Status::where('name', $status)->delete();

        return response()->json([
            'message' => __('status deleted successfully.'),
            'status' => HttpFoundationResponse::HTTP_OK
        ]);
    }
}
