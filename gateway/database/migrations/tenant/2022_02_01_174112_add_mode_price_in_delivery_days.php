<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModePriceInDeliveryDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_days', function (Blueprint $table) {
            $table->enum('mode', ['percentage', 'fixed']);
            $table->integer('price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_days', function (Blueprint $table) {
            $table->dropColumn('mode');
            $table->dropColumn('price');
        });
    }
}
