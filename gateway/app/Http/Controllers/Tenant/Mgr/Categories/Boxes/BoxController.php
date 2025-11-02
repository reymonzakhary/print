<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boxes\BoxStoreRequest;
use App\Http\Requests\Boxes\Printing\StoreBoxRequest;
use App\Http\Requests\Boxes\Printing\UpdateBoxRequest;
use App\Http\Resources\Boxes\PrintBoxResource;
use App\Http\Resources\Options\PrintOptionResource;
use App\Services\Suppliers\SupplierBoxService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BoxController extends Controller
{
    /**
     * ContextController constructor.
     * @param SupplierBoxService $supplierBoxService
     */
    public function __construct(public SupplierBoxService $supplierBoxService)
    {
    }

    /**
     * @OA\Get(
     *     tags={"Boxes"},
     *     path="/api/v1/mgr/boxes",
     *     summary="get boxs list",
     *     security={{ "Bearer":{} }},
     *     @OA\Parameter(
     *        name="page",
     *        in="query",
     *        required=false,
     *        description="page number",
     *        @OA\Schema(
     *            type="int64"
     *        ),
     *     ),
     *     @OA\Parameter(
     *        name="per_page",
     *        in="query",
     *        required=false,
     *        description="number of options per_page",
     *        @OA\Schema(
     *            type="int64"
     *        ),
     *     ),
     *     @OA\Parameter(
     *        name="filter",
     *        in="query",
     *        required=false,
     *        description="Filter options by name",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *     ),
     *     @OA\Parameter(
     *        name="sort_by",
     *        in="query",
     *        required=false,
     *        description="Filter options by name",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *     ),
     *     @OA\Parameter(
     *        name="filter",
     *        in="query",
     *        required=false,
     *        description="Filter options by name",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *     ),
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          in="header",
     *          name="bearerAuth",
     *          type="oauth2",
     *          scheme="passport",
     *          bearerFormat="JWT",
     *     ),
     *     @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/PrintBoxResource")))
     * )
     *
     * @return PrintOptionResource
     */
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function index(Request $request)
    {
        try {
            $proxy = $this->supplierBoxService->obtainBoxes($request->all());
            return PrintBoxResource::collection($proxy);
        } catch (Exception $e) {
            /**
             * error response
             */
            return response()->json([
                'message' => __('boxes.bad_request'),
                'status' => Response::HTTP_BAD_REQUEST

            ], Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * @param string $box
     * @return PrintBoxResource|JsonResponse
     * @throws GuzzleException
     */
    public function show(
        string $box
    )
    {
        $proxy = $this->supplierBoxService->obtainBox($box);

        if(optional($proxy)['status'] !== Response::HTTP_OK) {
            /**
             * error response
             */
            return response()->json([
                'message' => __($proxy['message']),
                'status' => Response::HTTP_NOT_FOUND

            ], Response::HTTP_NOT_FOUND);
        }

        $old_media = collect($proxy['data'])->pluck('media')->first();
        $media = $old_media??[];
        foreach($media as $key => $value){
            if (!Storage::disk('assets')->exists(tenant()?->uuid .'/' . $value ) ){
                unset($media[$key]);
            }
        }

        if (!empty(array_diff($old_media, $media)))  {
            $proxy = $this->supplierBoxService->obtainUpdateBox(['media' => $media], $box);
            $proxy = $this->supplierBoxService->obtainBox($box);
        }

        return PrintBoxResource::make(...$proxy['data'])
            ->additional([
                'message' => '',
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * @OA\Post(
     *   tags={"Boxes"},
     *   path="/api/v1/mgr/boxes",
     *   summary="create Boxs",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/StoreBoxRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintBoxResource")),
     *          @OA\Property(type="array", title="options", description="options", property="options", @OA\Items(
     *              @OA\Property(type="string", title="data", description="data", property="data", example="[]"),
     *              @OA\Property(type="string", title="links", description="links", property="links", example="[]"),
     *              @OA\Property(type="string", title="meta", description="meta", property="meta", example="[]")
     *          )),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Box has been created successfully"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found"),
     *   @OA\Response(response=422, description="Unprocessable Entity",
     *     @OA\JsonContent(
     *          @OA\Property(format="string", title="message", example="Box has been taken.", description="message", property="message"),
     *          @OA\Property(format="string", title="status", example="422", description="status", property="status"),
     *     ),
     *   )
     * )
     */
    /**
     * @param StoreBoxRequest $request
     * @return PrintBoxResource|JsonResponse
     */
    public function store(
        StoreBoxRequest $request
    )
    {
        try {
            $response = $this->supplierBoxService->obtainStoreBox($request->validated(), ['options' => $request->get('options')]);
            if ($response['status'] === Response::HTTP_CREATED) {
                return PrintBoxResource::make($response['data'])->additional([
                    "message" => __("Box has been created successfully"),
                    "status" => Response::HTTP_CREATED,
                ]);
            }
            return response()->json([
                'message' => __('Box has been taken.'),
                "status" => $response->getStatusCode(),
            ], $response->getStatusCode());
        } catch (Exception $e) {
            /**
             * error response
             */
            return response()->json([
                'message' => __('boxes.bad_request'),
                'status' => Response::HTTP_BAD_REQUEST

            ], Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * @OA\Put(
     *   tags={"Boxes"},
     *   path="/api/v1/mgr/boxes/{box_slug}",
     *   summary="update Box",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      description="Update box data",
     *      @OA\JsonContent(ref="#/components/schemas/UpdateBoxRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintBoxResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Box has been created successfully"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    /**
     * @param UpdateBoxRequest $request
     * @param string $box
     * @return PrintBoxResource|JsonResponse
     */
    public function update(
        UpdateBoxRequest $request,
        string           $box
    )
    {
        try {
            $response = $this->supplierBoxService->obtainUpdateBox($request->validated(), $box);
            if (is_array($response)) {
                return PrintBoxResource::make($response)->additional([
                    "message" => __("Box has been updated succssfuly"),
                    "status" => Response::HTTP_OK,
                ]);
            }
            $response = json_decode($response->getBody()->getContents());
            return response()->json([
                'message' => $response->message,
                "status" => $response->status
            ], $response->status);
        } catch (Exception $e) {
            /**
             * error response
             */
            return response()->json([
                'message' => $e->getMessage(),
                'status' => Response::HTTP_BAD_REQUEST

            ], Response::HTTP_BAD_REQUEST);
        }


    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(
        string $id
    )
    {

        // @todo Remove category from mongo db
        return response()->json([
            'data' => [
                'message' => 'you have to update this function to remove from other db '
            ]
        ], Response::HTTP_OK);

    }

}
