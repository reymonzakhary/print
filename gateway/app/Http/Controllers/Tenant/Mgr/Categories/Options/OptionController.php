<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Options;

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
     *     tags={"Category Option Configurations"},
     *     path="/api/v1/mgr/categories/{category_id}/options/{option_id}",
     *     summary="get category option configure list",
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
     *
     *     @OA\Parameter(
     *        name="Category ID",
     *        in="query",
     *        required=false,
     *        description="Category ID to get option configuration",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *     ),
     *
     *     @OA\Parameter(
     * *        name="Option ID",
     * *        in="query",
     * *        required=false,
     * *        description="Option ID to get option configuration",
     * *        @OA\Schema(
     * *            type="string"
     * *        ),
     * *     ),
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
     * @param string $category_id
     * @param string $option_id
     * @return PrintOptionResource|JsonResponse
     */
    public function show(
        Request $request,
        string $category_id,
        string $option_id
    )
    {
        $response = $this->optionService->obtainCategoryOption($request->all(), $category_id , $option_id);
        if($response['status'] !== Response::HTTP_OK) {
            return response()->json([
                'message' => $response['message'],
                "status" => $response['status']
            ], $response['status']);
        }
        return PrintOptionResource::make(
            $response['data'] ?? []
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @OA\Put(
     *   tags={"Options"},
     *   path="/api/v1/mgr/options/{option}",
     *   summary="create options",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *     @OA\Parameter(
     *        name="Category ID",
     *        in="query",
     *        required=True,
     *        description="Category ID to attach to option configuration",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *     ),
     *
     *     @OA\Parameter(
     *         name="Option ID",
     *         in="query",
     *         required=True,
     *         description="Option ID to attach to configuration",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *      ),
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(ref="#/components/schemas/UpdateOptionRequest"),
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
     * @param UpdateOptionRequest $request
     * @param string              $category_id
     * @param string              $optionId
     * @return PrintOptionResource|JsonResponse
     */
    public function update(
        UpdateOptionRequest $request,
        string              $category_id,
        string              $optionId
    ): PrintOptionResource|JsonResponse
    {
        try {
            $response = $this->optionService->obtainUpdateCategoryOption($request->validated(), $category_id, $optionId);

            if ($response['status'] !== Response::HTTP_OK || !isset($response['data'])) {
                return response()->json([
                    'message' => $response['message'],
                    "status" => $response['status']
                ], $response['status']);

            }

            return PrintOptionResource::make(
                $response['data']
            )->additional([
                "message" => __("Option has been updated successfully."),
                "status" => Response::HTTP_OK,
            ]);

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
     * @OA\Put(
     *   tags={"Options"},
     *   path="/api/v1/mgr/options/{option}",
     *   summary="detach category from option configuration",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *     @OA\Parameter(
     *        name="Category ID",
     *        in="query",
     *        required=True,
     *        description="Category ID to detach from option configuration",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *     ),
     *
     *     @OA\Parameter(
     *         name="Option ID",
     *         in="query",
     *         required=True,
     *         description="Option ID to detach from category",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *      ),
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
     * @param string              $categoryId
     * @param string              $optionId
     * @return JsonResponse
     */
    public function destroy(
        string $categoryId,
        string $optionId
    ): JsonResponse
    {
        try {
            $response = $this->optionService->obtainDeleteCategoryOption($categoryId, $optionId);
            if (isset($response['status']) && $response['status'] !== Response::HTTP_OK) {
                return response()->json([
                    'message' => $response['message'],
                    "status" => $response['status']
                ], $response['status']);
            }
            return response()->json([
                'message' => __("Option Configure has been deleted successfully."),
                "status" => Response::HTTP_OK,
            ]);
        }catch (Exception $e) {
            return response()->json([
                'message' => __('options.bad_request'),
                'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
