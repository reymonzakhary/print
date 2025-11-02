<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->index();
            $table->string('description')->nullable();
            $table->string('art_num')->nullable()->index();
            $table->integer('sort')->nullable();

            $table->integer('margin_value')->nullable();
            $table->enum('margin_type', ['fixed', 'percentage'])->nullable();
            $table->integer('discount_value')->nullable(); // ex tax
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable(); // ex tax
//            $table->integer('price')->nullable(); // ex tax


            $table->date('sale_start_at')->nullable();
            $table->date('sale_end_at')->nullable();


            $table->boolean('free')->default(false);
            $table->jsonb('properties')->nullable();

            $table->boolean('stock_product')->nullable()->default(false);
            $table->boolean('excludes')->nullable()->default(false);

            $table->boolean('variation')->nullable()->default(false);
            $table->boolean('combination')->nullable()->default(false);

            $table->foreignId('vat_id')->nullable()->index();
            $table->foreignId('unit_id')->nullable()->index();

            $table->foreignId('parent_id')->nullable()->index();
            $table->foreignId('brand_id')->nullable()->index();
            $table->foreignId('category_id')->index();
            $table->string('iso', 3)->nullable();
            $table->foreignId('row_id')->unsigned()->index()->nullable();
            $table->boolean('published')->default(true);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->date('expire_date')->nullable();
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
        Schema::dropIfExists('products');
    }
}
