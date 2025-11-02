<?php

namespace App\Http\Controllers\Tenant\Mgr\Warehouses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouses\StoreWarehouseRequest;
use App\Http\Requests\Warehouses\UpdateWarehouseRequest;
use App\Http\Resources\Warehouses\WarehouseResource;
use App\Models\Tenants\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *   tags={"Warehouses"},
     *   path="/api/v1/mgr/warehouses",
     *   summary="list all warehouses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     * @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/WarehouseResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     * )
     */
    public function index()
    {
        return WarehouseResource::collection(
            Warehouse::paginate(request()->get('perPage') ?? 10)
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWarehouseRequest $request
     * @return WarehouseResource
     *
     * @OA\Post(
     *   tags={"Warehouses"},
     *   path="/api/v1/mgr/warehouses",
     *   summary="create warehouses",
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
     *      description="insert users data",
     *      @OA\JsonContent(ref="#/components/schemas/StoreWarehouseRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/WarehouseResource"))),
     * )
     */
    public function store(
        StoreWarehouseRequest $request
    )
    {
        return WarehouseResource::make(
            Warehouse::create($request->validated())
        )
            ->additional([
                'message' => __('Warehouse added successfully'),
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Warehouse $id
     * @return WarehouseResource
     *
     * @OA\Get(
     *   tags={"Warehouses"},
     *   path="/api/v1/mgr/warehouses/{id}",
     *   summary="show Warehouses details",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/WarehouseResource"))),
     * )
     */
    public function show(
        Warehouse $warehouse
    )
    {
        return WarehouseResource::make(
            $warehouse
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateWarehouseRequest $request
     * @param Warehouse              $warehouse
     * @return WarehouseResource
     *
     * @OA\Put(
     *   tags={"Warehouses"},
     *   path="/api/v1/mgr/warehouses/{id}",
     *   summary="update Warehouse",
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
     *      description="insert users data",
     *      @OA\JsonContent(ref="#/components/schemas/UpdateWarehouseRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/WarehouseResource"))),
     * )
     */

    public function update(
        UpdateWarehouseRequest $request,
        Warehouse              $warehouse
    )
    {
        if ($warehouse->update($request->validated())) {
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => __('Warehouse has been updated successfully.'),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We could\'nt handle this request!'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Warehouse $warehouse
     * @return JsonResponse
     *
     * @OA\Delete(
     *   tags={"Warehouses"},
     *   path="/api/v1/mgr/warehouses/{id}",
     *   summary="delete Warehouses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     * @OA\Response(
     *    response=200,
     *    description="user deleted with success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="user removed"),
     *   )
     * ),
     * )
     */
    public function destroy(
        Warehouse $warehouse
    )
    {

        if (
            $warehouse->removeAddressIfExists() &&
            $warehouse->removeLocationsIfExists() &&
            $warehouse->delete()
        ) {
            return response()->json([
                'data' => [
                    'message' => __('Warehouse was been deleted'),
                    'status' => Response::HTTP_OK,
                ]
            ], Response::HTTP_OK);
        }
        return response()->json([
            'data' => [
                'message' => __('we can\'t Delete Warehouse'),
                'status' => Response::HTTP_BAD_REQUEST,
            ]
        ], Response::HTTP_BAD_REQUEST);

    }
}
