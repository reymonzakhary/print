<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('description')->nullable();
            $table->foreignId('parent_id')->unsigned()->index()->nullable();
            $table->integer('sort')->nullable();
            $table->string('iso', 3)->nullable();
            $table->foreignId('base_id')->unsigned()->index()->nullable();
            $table->foreignId('row_id')->unsigned()->index()->nullable();

            $table->integer('margin_value')->nullable();
            $table->enum('margin_type', ['fixed', 'percentage'])->nullable();

            $table->integer('discount_value')->nullable(); // ex tax
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable(); // ex tax

            $table->boolean('published')->default(true);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unique(['name', 'iso']);
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
        Schema::dropIfExists('categories');
    }
}
