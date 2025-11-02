<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ctx_id')->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('sort')->default(0);
            $table->boolean('secure_variable')->default(false);
            $table->string('namespace')->nullable()->default('core');
            $table->string('area')->nullable();
            $table->string('lexicon')->nullable();

            $table->string('name')->nullable();
            $table->string('key')->index();
            $table->text('value')->nullable();

            $table->string('data_type')->default('string'); // string || Boolean || Array || datetime || float || integer
            $table->string('data_variable')->nullable();
            $table->boolean('multi_select')->nullable();
            $table->boolean('incremental')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
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
        Schema::dropIfExists('user_settings');
    }
}
