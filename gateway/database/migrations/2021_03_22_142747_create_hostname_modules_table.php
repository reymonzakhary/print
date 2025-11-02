<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHostnameModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hostname_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('hostname_id');
            $table->timestamps();

            $table->foreign('module_id')->references('id')
                ->on('modules')->cascadeOnDelete()->onUpdate('cascade');
            $table->foreign('hostname_id')->references('id')
                ->on('hostnames')->cascadeOnDelete()->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hostname_modules');
    }
}
