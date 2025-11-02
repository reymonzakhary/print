<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable PostGIS extension - PostgreSQL 17 has better support for extensions
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->string('tenant_id');
            // Add JSON field as a fallback
            $table->json('polygon_json')->nullable();
        });

        // Add the geometry column for polygon
        // PostgreSQL 17 with PostGIS 3.4 has improved spatial indexing
        DB::statement('ALTER TABLE delivery_zones ADD COLUMN polygon GEOMETRY(POLYGON, 4326)');
        DB::statement('CREATE INDEX delivery_zones_polygon_idx ON delivery_zones USING GIST (polygon)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
