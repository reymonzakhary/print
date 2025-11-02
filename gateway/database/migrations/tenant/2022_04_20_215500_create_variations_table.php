<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->index();
            $table->foreignId('box_id')->nullable()->index();
            $table->foreignId('option_id')->nullable()->index();
            $table->uuid('sku')->index()->nullable();
            $table->foreignId('sku_id')->index()->nullable();
            $table->string('input_type')->default('text'); // select file
            $table->integer('margin_value')->nullable();
            $table->enum('margin_type', ['fixed', 'percentage'])->nullable();
            $table->integer('discount_value')->nullable(); // ex tax
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable(); // ex tax
            $table->bigInteger('price')->nullable(); // ex tax

            $table->boolean('single')->default(false)->nullable();
            $table->integer('upto')->nullable();
            $table->string('mime_type')->nullable();

            $table->foreignId('parent_id')->index()->nullable();
            $table->integer('sort')->nullable();
            $table->boolean('incremental')->nullable()->default(false);
            $table->integer('incremental_by')->nullable()->default(0);
            $table->boolean('published')->default(true);
            $table->boolean('override')->nullable()->default(false);
            $table->boolean('default_selected')->nullable()->default(false);
            $table->boolean('switch_price')->nullable()->default(false);
            $table->jsonb('properties')->nullable();
            $table->date('expire_date')->nullable();
            $table->boolean('appendage')->nullable()->default('false');
            $table->boolean('child')->nullable()->default('false');
            $table->integer('expire_after')->nullable();

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
        Schema::dropIfExists('variations');
    }
}
