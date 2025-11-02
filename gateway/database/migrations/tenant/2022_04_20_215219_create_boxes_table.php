<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('slug')->index();
            $table->string('input_type')->default('single');
            $table->boolean('incremental')->default(false);
            $table->integer('select_limit')->nullable();
            $table->integer('option_limit')->nullable();
            $table->foreignId('parent_id')->unsigned()->index()->nullable();
            $table->boolean('sqm')->default(false);
            $table->boolean('appendage')->nullable()->default(false);
            $table->integer('sort')->nullable();
            $table->string('iso', 3)->nullable();
            $table->foreignId('base_id')->unsigned()->index()->nullable();
            $table->foreignId('row_id')->unsigned()->index()->nullable();
            $table->foreignId('created_by')->nullable();
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
        Schema::dropIfExists('boxes');
    }
}
