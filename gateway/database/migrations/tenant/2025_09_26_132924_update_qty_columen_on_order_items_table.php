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
        // Store the view definition
        $viewDefinition = DB::select("SELECT pg_get_viewdef('product_stock_view') as definition")[0]->definition;

        // Drop the view
        DB::statement('DROP VIEW IF EXISTS product_stock_view');

        // Now alter the column
        Schema::table('order_items', function (Blueprint $table) {
            $table->bigInteger('qty')->change();
        });

        // Recreate the view
        DB::statement("CREATE VIEW product_stock_view AS {$viewDefinition}");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Store the view definition
        $viewDefinition = DB::select("SELECT pg_get_viewdef('product_stock_view') as definition")[0]->definition;

        // Drop the view
        DB::statement('DROP VIEW IF EXISTS product_stock_view');

        // Revert the column
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('qty')->change(); // or whatever the original type was
        });

        // Recreate the view
        DB::statement("CREATE VIEW product_stock_view AS {$viewDefinition}");
    }
};
