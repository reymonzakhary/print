<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('name'); /////// A4  }}}}}} // JA Of NEE
            $table->string('description')->nullable();
            $table->string('slug')->index(); // ja of nee
            $table->foreignId('box_id')->index();
            $table->string('input_type')->default('text'); // select file
            $table->integer('incremental_by')->nullable();
            $table->integer('min')->nullable(); //// 0
            $table->integer('max')->nullable(); /// 10000
            $table->decimal('width', 5, 4)->nullable(); /// 197
            $table->decimal('height', 5, 4)->nullable(); /// 279
            $table->decimal('length', 5, 4)->nullable(); /// 279

            $table->string('unit', 50)->default('mm')->nullable();

            $table->boolean('single')->default(false)->nullable();
            $table->integer('upto')->nullable();
            $table->string('mime_type')->nullable();

            $table->integer('margin_value')->nullable();
            $table->enum('margin_type', ['fixed', 'percentage'])->nullable();
            $table->integer('discount_value')->nullable(); // ex tax
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable(); // ex tax
            $table->bigInteger('price')->nullable(); // ex tax

            $table->boolean('price_switch')->nullable()->default(false);
            $table->integer('sort')->nullable();
            $table->boolean('secure')->nullable()->default(false);
            $table->foreignId('parent_id')->unsigned()->index()->nullable();
            $table->jsonb('properties')->nullable();

            $table->string('iso', 3)->nullable();
            $table->foreignId('base_id')->unsigned()->index()->nullable();
            $table->foreignId('row_id')->unsigned()->index()->nullable();
            $table->foreignId('created_by')->nullable();
            $table->unique(['name', 'box_id', 'iso']);
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
        Schema::dropIfExists('options');
    }
}
