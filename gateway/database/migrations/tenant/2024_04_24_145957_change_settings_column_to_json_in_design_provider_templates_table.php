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
        Schema::table('design_provider_templates', function (Blueprint $table) {
            DB::statement('ALTER TABLE design_provider_templates ALTER COLUMN settings TYPE json USING (settings)::json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('design_provider_templates', function (Blueprint $table) {
            $table->string('settings')->change();
        });
    }
};
