<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->index()->nullable();
            $table->string('ean')->index()->nullable();
            $table->integer('low_qty_threshold')->index()->nullable();
            $table->integer('high_qty_threshold')->index()->nullable();
            $table->date('open_stock')->nullable();
            $table->bigInteger('price')->nullable(); // ex tax
            $table->date('sale_start_at')->nullable();
            $table->date('sale_end_at')->nullable();
            $table->integer('sort')->nullable();
            $table->foreignId('parent_id')->nullable()->index();
            $table->bigInteger('product_id')->nullable()->index();
            $table->string('option_id')->nullable()->index();
            $table->boolean('published')->nullable()->default(true);
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
        Schema::dropIfExists('skus');
    }
}
