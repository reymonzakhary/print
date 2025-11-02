<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_variables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')->index();
            $table->string('name')->index();
            $table->string('key');
            $table->string('label');

            $table->unsignedBigInteger('folder_id')->nullable();

            $table->foreign('folder_id')
                ->references('id')
                ->on('folders')
                ->onUpdate('cascade');

            $table->string('data_type')->nullable();
            $table->string('input_type')->nullable();
            $table->string('default_value')->nullable();
            $table->string('data_variable')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('class')->nullable();
            $table->string('secure_variable')->nullable();
            $table->string('multi_select')->nullable();
            $table->string('incremental')->nullable();
            $table->integer('min_count')->nullable();
            $table->integer('max_count')->nullable();
            $table->integer('min_size')->nullable();
            $table->integer('max_size')->nullable();
            $table->jsonb('properties')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_variables');
    }
};
