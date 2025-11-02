<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoopsPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boop_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('supplier_id');

            $table->unsignedBigInteger('category_id')->index();
            $table->foreign('category_id')->references('id')->on('assortments');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->string('boops_category_id')->nullable();

            $table->string('collection')->nullable();

            $table->json('tables');

            $table->string('boid')->nullable()->index();

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
        Schema::dropIfExists('boop_prices');
    }
}
