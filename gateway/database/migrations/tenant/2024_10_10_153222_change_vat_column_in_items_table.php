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
        // Step 1: Drop the foreign key constraint from items table
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['vat_id']);
        });

        // Step 2: Alter vat_id in items table to NUMERIC(5, 2)
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('vat_id', 5, 2)->nullable()->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->renameColumn('vat_id', 'vat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the operations in the down method
        Schema::table('items', function (Blueprint $table) {
            $table->renameColumn('vat', 'vat_id');

        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('vat_id')->nullable()->change();
        });
        DB::table('items')
            ->where('vat_id', 0.00)
            ->update(['vat_id' => null]);
        Schema::table('items', function (Blueprint $table) {
            // Revert the column back to bigint if needed in the rollback
            $table->foreign('vat_id')->references('id')->on('vats')->onDelete('set null');
        });
    }
};
