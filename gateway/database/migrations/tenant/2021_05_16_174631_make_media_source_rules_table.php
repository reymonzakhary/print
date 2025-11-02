<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeMediaSourceRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_source_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreignId('media_source_id')->nullable();
            $table->string('disk')->default('tenancy');
            $table->string('path');
            $table->tinyInteger('access');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('media_source_id')->references('id')->on('media_sources')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_source_rules');
    }
}
