<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class DeliveryZone extends Model
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'active',
        'polygon_json',
        'tenant_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'polygon_json' => 'array',
    ];


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Hostname::class, 'tenant_id');
    }

    /**
     * @param array $coordinates
     * @return void
     */
    public function storePolygon(
        array $coordinates
    ): void
    {
        // Store in JSON field as backup
        $this->polygon_json = $coordinates;
        $this->save();

        // Format for PostGIS storage
        $pointsStr = [];
        foreach ($coordinates as $point) {
            $pointsStr[] = "{$point['lng']} {$point['lat']}";
        }

        // Close the polygon by repeating the first point if needed
        if ($pointsStr[0] !== $pointsStr[count($pointsStr) - 1]) {
            $pointsStr[] = $pointsStr[0];
        }

        $polygonStr = "POLYGON((" . implode(',', $pointsStr) . "))";

        // Update the PostGIS field - PostgreSQL 17 optimizes this operation
        DB::statement(
            "UPDATE delivery_zones SET polygon = ST_SetSRID(ST_GeomFromText(?), 4326) WHERE id = ?",
            [$polygonStr, $this->id]
        );
    }

    /**
     * Determine if a given point is contained within a delivery zone polygon.
     *
     * @param float $latitude The latitude of the point
     * @param float $longitude The longitude of the point
     * @return bool Whether the point is contained within the delivery zone polygon
     */
    public function containsPoint(
        float $latitude,
        float $longitude
    ): bool
    {
        $result = DB::selectOne(
            "SELECT ST_Contains(polygon, ST_SetSRID(ST_Point(?, ?), 4326)) as contains FROM delivery_zones WHERE id = ?",
            [$longitude, $latitude, $this->id]
        );

        return $result && $result->contains;
    }

    /**
     * Find all zones containing a point
     * Uses spatial index created in migration
     *
     * @param float $latitude
     * @param float $longitude
     * @return Collection
     */
    public static function zonesContainingPoint($latitude, $longitude)
    {
        $zoneIds = DB::select(
            "SELECT id FROM delivery_zones
            WHERE ST_Contains(polygon, ST_SetSRID(ST_Point(?, ?), 4326))
            AND active = true",
            [$longitude, $latitude]
        );

        $ids = array_map(function($item) {
            return $item->id;
        }, $zoneIds);

        return self::whereIn('id', $ids)->get();
    }

    /**
     * Find stores within a certain distance of a point and inside a delivery zone
     * Uses PostgreSQL 17's improved spatial functions
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $distanceInKm
     * @return array
     */
    public static function findNearbyStores(
        float $latitude,
        float $longitude,
        float $distanceInKm = 5.0
    ): array
    {
        return DB::select(
            "SELECT s.id, s.name, s.address,
            ST_Distance(
                s.location::geography,
                ST_SetSRID(ST_Point(?, ?), 4326)::geography
            ) / 1000 as distance_km
            FROM stores s
            JOIN delivery_zones dz ON ST_Intersects(dz.polygon, s.location)
            WHERE dz.active = true
            AND ST_DWithin(
                s.location::geography,
                ST_SetSRID(ST_Point(?, ?), 4326)::geography,
                ? * 1000
            )
            ORDER BY distance_km ASC",
            [$longitude, $latitude, $longitude, $latitude, $distanceInKm]
        );
    }

    /**
     * Check if a store is within this delivery zone
     *
     * @param int $storeId
     * @return bool
     */
    public function containsStore(
        int $storeId
    ): bool
    {
        $result = DB::selectOne(
            "SELECT ST_Contains(dz.polygon, s.location) as contains
            FROM delivery_zones dz, stores s
            WHERE dz.id = ? AND s.id = ?",
            [$this->id, $storeId]
        );

        return $result && $result->contains;
    }

    /**
     * Calculate area of the delivery zone in square kilometers
     * PostgreSQL 17 has improved geographic calculations
     *
     * @return float|int
     */
    public function getAreaInSquareKm(): float|int
    {
        $result = DB::selectOne(
            "SELECT ST_Area(polygon::geography) / 1000000 as area_km2
            FROM delivery_zones WHERE id = ?",
            [$this->id]
        );

        return $result ? $result->area_km2 : 0;
    }

    /**
     * Find zones that overlap with this zone
     *
     * @return Collection
     */
    public function findOverlappingZones(): Collection
    {
        $zoneIds = DB::select(
            "SELECT dz.id
            FROM delivery_zones dz
            WHERE dz.id != ?
            AND ST_Overlaps(dz.polygon, (SELECT polygon FROM delivery_zones WHERE id = ?))",
            [$this->id, $this->id]
        );

        $ids = array_map(function($item) {
            return $item->id;
        }, $zoneIds);

        return self::whereIn('id', $ids)->get();
    }
}
