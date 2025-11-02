<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropUnique(['address', 'number', 'city', 'region', 'country_id']);

            $table->unique(['address', 'number', 'city', 'region', 'country_id', 'zip_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropUnique(['address', 'number', 'city', 'region', 'country_id', 'zip_code']);

            $table->unique(['address', 'number', 'city', 'region', 'country_id']);

        });
    }
};
