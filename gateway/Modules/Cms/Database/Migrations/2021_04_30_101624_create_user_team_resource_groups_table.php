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
        Schema::create('user_team_resource_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('team_id')->index();
            $table->unsignedBigInteger('resource_group_id')->index();
            $table->foreign('team_id')
                ->references('id')
                ->on('teams');
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
        Schema::dropIfExists('user_team_resource_groups');
    }
};
