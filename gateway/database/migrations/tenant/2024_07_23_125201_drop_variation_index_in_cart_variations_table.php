<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_variations', function (Blueprint $table) {
            $table->dropIndex('cart_variation_variation_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_variations', function (Blueprint $table) {
            $table->index('variation', 'cart_variation_variation_index');
        });
    }
};
