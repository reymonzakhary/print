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
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('external_id')->change();
            $table->string('internal_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            DB::statement('ALTER TABLE quotations ALTER COLUMN external_id TYPE INTEGER USING external_id::integer');
            DB::statement('ALTER TABLE quotations ALTER COLUMN internal_id TYPE INTEGER USING internal_id::integer');
        });
    }
};
