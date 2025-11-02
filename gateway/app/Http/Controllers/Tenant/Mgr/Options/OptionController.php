<?php

namespace App\Http\Controllers\Tenant\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Http\Requests\Options\Printing\StoreOptionRequest;
use App\Http\Requests\Options\Printing\UpdateOptionRequest;
use App\Http\Resources\Options\PrintOptionIndexResource;
use App\Http\Resources\Options\PrintOptionResource;
use App\Services\Options\OptionService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionController extends Controller
{
    /**
     * ContextController constructor.
     * @param OptionService $optionService
     */
    public function __construct(public OptionService $optionService)
    {
    }

    /**
     * @OA\Get(
     *     tags={"Options"},
     *     path="/api/v1/mgr/options",
     *     summary="get options list",
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
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintOptionResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Option has been updated succssfuly"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     *
     * @param Request $request
     *
     */
    public function index(
        Request $request
    )
    {
        return PrintOptionIndexResource::collection(
            $this->optionService->obtainOptions($request->all())
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @OA\Post(
     *   tags={"Options"},
     *   path="/api/v1/mgr/options",
     *   summary="create option general info",
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
     *      @OA\JsonContent(ref="#/components/schemas/StoreOptionRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintOptionResource")),
     *          @OA\Property(type="string", title="message", description="message", property="message", example="Option has been updated succssfuly"),
     *          @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    /**
     * @param StoreOptionRequest $request
     * @return PrintOptionResource|JsonResponse
     * @throws GuzzleException
     */
    public function store(
        StoreOptionRequest $request
    ): PrintOptionResource|JsonResponse
    {
        try {
            $response = $this->optionService->obtainStoreOption($request->validated()); // Add option general info only
            if (isset($response['status']) && $response['status'] !== Response::HTTP_CREATED || !isset($response['data'])) {
                return response()->json([
                    'message' => $response['message'],
                    "status" => $response['status']
                ], $response['status']);
            }
            
            return PrintOptionResource::make($response['data'])->additional([
                "message" => __("Option has been created successfully."),
                "status" => Response::HTTP_CREATED,
            ]);
        } catch (Exception $e) {
            /**
             * error response
             */
            return response()->json([
                'message' => __('Option.bad_request'),
                'status' => Response::HTTP_BAD_REQUEST

            ], Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * @param string $id
     * @return JsonResponse
     */
//    public function destroy(
//        string $id
//    )
//    {
//
//
//    }

}
