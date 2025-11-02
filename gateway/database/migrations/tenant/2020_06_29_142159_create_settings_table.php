<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ctx_id')->index();
            $table->integer('sort')->default(0);
            $table->boolean('secure_variable')->default(false);
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('namespace')->nullable()->default('core');
            $table->string('area')->nullable();
            $table->string('lexicon')->nullable();

            $table->string('key')->index();
            $table->text('value')->nullable();

            $table->string('data_type')->default('string'); // string || Boolean || Array || datetime || float || integer
            $table->string('data_variable')->nullable();
            $table->boolean('multi_select')->nullable();
            $table->boolean('incremental')->nullable();

            $table->foreign('ctx_id')->references('id')->on('contexts')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
