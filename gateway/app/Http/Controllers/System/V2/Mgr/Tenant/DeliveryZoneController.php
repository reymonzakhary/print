<?php

namespace App\Http\Controllers\System\V2\Mgr\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\DeliveryZoneRequest;
use App\Models\DeliveryZone;
use App\Models\Domain;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DeliveryZoneController extends Controller
{

    /**
     * Store delivery zones for a tenant (maintains FULL accuracy)
     *
     * @param DeliveryZoneRequest $request
     * @param Domain $tenant
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(
        DeliveryZoneRequest $request,
        Domain $tenant
    ): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Get validated zones data
            $zones = $request->getValidatedZones();

            $deliveryZonesSummary = [];
            $totalPoints = 0;

            foreach ($zones as $zoneData) {
                try {
                    $polygonPoints = $zoneData['polygon'];
                    $pointCount = count($polygonPoints);
                    $totalPoints += $pointCount;

                    // Create the delivery zone with FULL ACCURACY
                    $deliveryZone = DeliveryZone::create([
                        'name' => $zoneData['name'],
                        'description' => $zoneData['description'],
                        'active' => $zoneData['active'],
                        'polygon_json' => $polygonPoints, // Store ALL points
                        'tenant_id' => $tenant->id,
                    ]);

                    // Store the PostGIS polygon with ALL points
                    $deliveryZone->storePolygon($polygonPoints);

                    // Calculate area
                    $areaKm2 = $deliveryZone->getAreaInSquareKm();

                    // Check for overlapping zones
                    $overlappingZones = $deliveryZone->findOverlappingZones();

                    $deliveryZonesSummary[] = [
                        'id' => $deliveryZone->id,
                        'name' => $deliveryZone->name,
                        'description' => $deliveryZone->description,
                        'active' => $deliveryZone->active,
                        'points_count' => $pointCount,
                        'area_km2' => round($areaKm2, 2),
                        'overlapping_zones_count' => $overlappingZones->count(),
                        'overlapping_zones' => $overlappingZones->pluck('name')->toArray(),
                    ];

                    Log::info("Delivery zone created with full accuracy", [
                        'tenant_id' => $tenant->id,
                        'zone_id' => $deliveryZone->id,
                        'zone_name' => $deliveryZone->name,
                        'points_count' => $pointCount,
                        'area_km2' => $areaKm2,
                        'overlapping_zones' => $overlappingZones->count(),
                    ]);

                } catch (\Exception $e) {
                    Log::error("Failed to create delivery zone", [
                        'tenant_id' => $tenant->id,
                        'zone_name' => $zoneData['name'],
                        'points_count' => count($zoneData['polygon'] ?? []),
                        'error' => $e->getMessage(),
                    ]);

                    throw new \Exception("Failed to create delivery zone '{$zoneData['name']}': " . $e->getMessage());
                }
            }

            DB::commit();

            Log::info("All delivery zones created successfully", [
                'tenant_id' => $tenant->id,
                'zones_created' => count($deliveryZonesSummary),
                'total_points_preserved' => $totalPoints,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Delivery zones created successfully with full accuracy'),
                'data' => [
                    'tenant_id' => $tenant->id,
                    'zones_created' => count($deliveryZonesSummary),
                    'total_points_preserved' => $totalPoints,
                    'delivery_zones' => $deliveryZonesSummary,
                ],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Delivery zones creation failed", [
                'tenant_id' => $tenant->id,
                'zones_count' => count($zones ?? []),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to create delivery zones'),
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




    public function update(
        DeliveryZoneRequest $request,
        Domain $tenant
    ): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Get validated zones data
            $zones = $request->getValidatedZones();
            $deliveryZonesSummary = [];
            $totalPoints = 0;

            $tenantDeliveryZones = $tenant->deliveryZones()->select('id')->get();
            foreach ($tenantDeliveryZones as $tenantDeliveryZone) {
                if (!in_array($tenantDeliveryZone->id, array_column($zones, 'id'))) {
                    // Delete zones that are not in the request
                    $tenantDeliveryZone->delete();
                    Log::info("Deleted delivery zone", [
                        'tenant_id' => $tenant->id,
                        'zone_id' => $tenantDeliveryZone->id,
                    ]);
                }
            }



            foreach ($zones as $zoneData) {
                try {
                    $polygonPoints = $zoneData['polygon'];
                    $pointCount = count($polygonPoints);
                    $totalPoints += $pointCount;

                    // Create the delivery zone with FULL ACCURACY
                    $deliveryZone = DeliveryZone::updateOrCreate([
                        'id' => $zoneData['id'] ?? null,
                        'tenant_id' => $tenant->id,
                    ] , [
                        'name' => $zoneData['name'],
                        'description' => $zoneData['description'],
                        'active' => $zoneData['active'],
                        'polygon_json' => $polygonPoints, // Store ALL points
                    ]);

                    // Store the PostGIS polygon with ALL points
                    $deliveryZone->storePolygon($polygonPoints);

                    // Calculate area
                    $areaKm2 = $deliveryZone->getAreaInSquareKm();

                    // Check for overlapping zones
                    $overlappingZones = $deliveryZone->findOverlappingZones();

                    $deliveryZonesSummary[] = [
                        'id' => $deliveryZone->id,
                        'name' => $deliveryZone->name,
                        'description' => $deliveryZone->description,
                        'active' => $deliveryZone->active,
                        'points_count' => $pointCount,
                        'area_km2' => round($areaKm2, 2),
                        'overlapping_zones_count' => $overlappingZones->count(),
                        'overlapping_zones' => $overlappingZones->pluck('name')->toArray(),
                    ];

                    Log::info("Delivery zone updated with full accuracy", [
                        'tenant_id' => $tenant->id,
                        'zone_id' => $deliveryZone->id,
                        'zone_name' => $deliveryZone->name,
                        'points_count' => $pointCount,
                        'area_km2' => $areaKm2,
                        'overlapping_zones' => $overlappingZones->count(),
                    ]);

                } catch (\Exception $e) {
                    Log::error("Failed to updated delivery zone " . $zoneData['id'] , [
                        'tenant_id' => $tenant->id,
                        'zone_name' => $zoneData['name'],
                        'points_count' => count($zoneData['polygon'] ?? []),
                        'error' => $e->getMessage(),
                    ]);

                    throw new \Exception("Failed to update delivery zone '{$zoneData['name']}': " . $e->getMessage());
                }
            }

            DB::commit();

            Log::info("All delivery zones updated successfully", [
                'tenant_id' => $tenant->id,
                'zones_created' => count($deliveryZonesSummary),
                'total_points_preserved' => $totalPoints,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Delivery zones updated successfully with full accuracy'),
                'data' => [
                    'tenant_id' => $tenant->id,
                    'zones_created' => count($deliveryZonesSummary),
                    'total_points_preserved' => $totalPoints,
                    'delivery_zones' => $deliveryZonesSummary,
                ],
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Delivery zones update failed", [
                'tenant_id' => $tenant->id,
                'zones_count' => count($zones ?? []),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to update delivery zones'),
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
