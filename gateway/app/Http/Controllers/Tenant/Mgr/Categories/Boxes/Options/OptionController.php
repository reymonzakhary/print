<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Boxes\Options;

use App\Http\Controllers\Controller;
use App\Http\Requests\Options\Printing\StoreOptionRequest;
use App\Http\Requests\Options\Printing\UpdateOptionRequest;
use App\Http\Resources\Options\PrintOptionResource;
use App\Services\Suppliers\SupplierBoxOptionService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated This script is deprecated and will be removed in a future version.
 *             Please migrate to the Mgr\Options\OptionController.php.

 */
class OptionController extends Controller
{
    /**
     * ContextController constructor.
     * @param SupplierBoxOptionService $supplierBoxOptionService
     */
    public function __construct(public SupplierBoxOptionService $supplierBoxOptionService)
    {
    }

    /**
     * @OA\Get(
     *     tags={"Boxes"},
     *     path="/api/v1/mgr/boxes/{box_slug}/options",
     *     summary="get box options list",
     *     security={{ "Bearer":{} }},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          in="header",
     *          name="bearerAuth",
     *          type="oauth2",
     *          scheme="passport",
     *          bearerFormat="JWT",
     *     ),
     *     @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/PrintOptionResource")))
     * )
     *
     * @return PrintOptionResource
     */
    /**
     * @param Request $request
     * @param string $box
     * @return JsonResponse
     */
    public function index(Request $request, string $box)
    {
        $proxy = $this->supplierBoxOptionService->obtainOptions($box, $request->all());
        if (!is_array($proxy)) {
            return response()->json([

                'message' => __("Something Wrong"),
                'status' => Response::HTTP_BAD_REQUEST

            ], Response::HTTP_BAD_REQUEST);
        }

        return PrintOptionResource::collection($proxy);


    }

    /**
     * @param Request $request
     * @param string $category
     * @param $option
     * @return PrintOptionResource|JsonResponse
     */
    public function show(Request $request, string $category, $option)
    {

        $proxy = $this->supplierBoxOptionService->obtainOption($category, $option, $request->all());

        if (!is_array(optional($proxy)['data'])) {
            return response()->json([
                'message' => __("Something Wrong"),
                'status' => Response::HTTP_BAD_REQUEST

            ], Response::HTTP_BAD_REQUEST);
        }

        $old_media = $proxy['data']['media'];
        $media = $old_media;
        foreach($media as $key => $value){
            if (!Storage::disk('assets')->exists(tenant()?->uuid .'/' . $value ) ){
                unset($media[$key]);
            }
        }
        if (!empty(array_diff($old_media, $media)))  {
            $proxy = $this->supplierBoxOptionService->obtainUpdateOption(['media' => $media], $category, $option);
            $proxy = $this->supplierBoxOptionService->obtainOption($category, $option, $request->all());
        }

        return PrintOptionResource::make($proxy['data']);
    }

    /**
     * @OA\Post(
     *   tags={"Boxes"},
     *   path="/api/v1/mgr/boxes/{box_slug}/options",
     *   summary="create Box`s options",
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
     *      @OA\JsonContent(ref="#/components/schemas/StoreOptionRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/PrintOptionResource")),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    /**
     * @return JsonResponse
     */
    public function store(
        StoreOptionRequest $request,
        string             $category,
    )
    {
        /**
         * FIXME Change obtainStoreOption to obtainStoreBoxOption
         */
        try {
            $response = $this->supplierBoxOptionService->obtainStoreBoxOption($category, $request->validated());

            return response()->json([
                'message' => 'option Created successfully',
                "status" => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
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
     * @param UpdateOptionRequest $request
     * @param string $category
     * @param string $option
     * @return JsonResponse
     */
    public function update(
        UpdateOptionRequest $request,
        string              $category,
        string              $option
    )
    {
        try {
            $response = $this->supplierBoxOptionService->obtainUpdateOption($request->validated(), $category, $option);
            return response()->json([
                'message' => __("Option has been updated successfully."),
                "status" => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            /**
             * error response
             */
            return response()->json([
                'message' => __('options.bad_request'),
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
