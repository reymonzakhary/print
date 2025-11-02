<?php

namespace App\Http\Controllers\Tenant\Mgr\Warehouses\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouses\Locations\StoreLocationRequest;
use App\Http\Requests\Warehouses\Locations\UpdateLocationRequest;
use App\Http\Resources\Warehouses\Locations\LocationResource;
use App\Models\Tenants\Location;
use App\Models\Tenants\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;


class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/locations",
     *   summary="list all addresses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/LocationResource"))),
     * )
     */
    public function index(
        Warehouse $warehouse
    )
    {
        return LocationResource::collection($warehouse->locations()->get())->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return LocationResource
     *
     * @OA\Post (
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/locations",
     *   summary="list all addresses",
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
     *      @OA\JsonContent(ref="#/components/schemas/StoreLocationRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/LocationResource"))),
     * )
     */
    public function store(
        Warehouse            $warehouse,
        StoreLocationRequest $request,
    )
    {
        return LocationResource::make($warehouse->locations()->create($request->validated()))
            ->additional([
                'message' => __('Location added successfully'),
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Warehouse $warehouse
     * @param int       $location
     * @return LocationResource
     */
    public function show(
        Warehouse $warehouse,
        Location  $location
    )
    {
        return LocationResource::make(
            $warehouse->locations()->where('id', $location->id)->first()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Warehouse             $warehouse
     * @param UpdateLocationRequest $request
     * @param Location              $location
     * @return JsonResponse
     *
     * @OA\PUT (
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/locations/{id}",
     *   summary="list all addresses",
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
     *      @OA\JsonContent(ref="#/components/schemas/UpdateLocationRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/LocationResource"))),
     * )
     */
    public function update(
        Warehouse             $warehouse,
        UpdateLocationRequest $request,
        Location              $location
    )
    {
        if (
            $warehouse->locations()
                ->where('id', $location->id)->first()
                ->update($request->validated())
        ) {
            return Response()->json([
                'status' => Response::HTTP_OK,
                'message' => __('Location updated successfully'),
            ], Response::HTTP_OK);
        }

        return Response()->json([
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => __('We can\'t update location'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Warehouse $warehouse
     * @param Location  $location
     * @return \Illuminate\Http\Response
     *
     * @OA\Delete (
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/locations/{id}",
     *   summary="Delete addresses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\Response(response="202", description="success",@OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Location removed successfully"),
     *     ))),
     * )
     */
    public function destroy(
        Warehouse $warehouse,
        Location  $location
    )
    {
        if ($warehouse->locations()->where('locations.id', $location->id)->delete()) {
            return Response()->json([
                'status' => Response::HTTP_OK,
                'message' => __('Location removed successfully'),
            ], Response::HTTP_OK);
        }
        return Response()->json([
            'status' => Response::HTTP_BAD_REQUEST,
            'message' => __('we can\'t delete warehouse location'),
        ], Response::HTTP_BAD_REQUEST);
    }
}
