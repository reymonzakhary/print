<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index(); // 1212
            $table->unsignedBigInteger('item_id')->index()->nullable(); // letterhead holland 21%
            $table->unsignedBigInteger('service_id')->index()->nullable();
            $table->unsignedBigInteger('vat_id')->index()->nullable(); // 1 =>  0 %

            $table->integer('qty')->unsigned()->nullable();
            $table->boolean('delivery_pickup')->nullable();
            $table->unsignedBigInteger('shipping_cost')->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->foreign('vat_id')->references('id')->on('vats');
            $table->foreign('service_id')->references('id')->on('services');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
