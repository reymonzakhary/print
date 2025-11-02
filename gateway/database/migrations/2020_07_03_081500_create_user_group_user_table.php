<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_group_id')->index();
            $table->unsignedBigInteger('user_id')->index();


            $table->foreign('user_id')->references('id')
                ->on('users')->cascadeOnDelete()->onUpdate('cascade');
            $table->foreign('user_group_id')->references('id')
                ->on('user_groups')->cascadeOnDelete()->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_user');
    }
}
