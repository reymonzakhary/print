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
        Schema::create('resource_resource_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id')->index();
            $table->unsignedBigInteger('resource_group_id')->index();
            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')
                ->cascadeOnDelete();
            $table->foreign('resource_group_id')
                ->references('id')
                ->on('resource_groups')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resource_resource_groups');
    }
};
