<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->jsonb('product');
            $table->unsignedBigInteger('vat_id')->index()->nullable();
            $table->string('reference', 100)->nullable();
            $table->boolean('discount_fixed')->default(false)->nullable();
            $table->boolean('delivery_separated')->default(false)->nullable();
            $table->integer('discount')->default(0)->nullable();
            $table->integer('st')->unsigned()->default(300);
            $table->uuid('supplier_id')->nullable();
            $table->string('note')->nullable();

            $table->foreign('vat_id')->references('id')->on('vats');
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
        Schema::dropIfExists('items');
    }
}
