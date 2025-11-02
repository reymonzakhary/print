<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address')->nullable();
            $table->char('number', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('zip_code')->nullable();
            $table->unsignedBigInteger('country_id')->index()->nullable();
            $table->float('lat', 10, 6)->index()->nullable();
            $table->float('lng', 10, 6)->index()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['address', 'number', 'city', 'region', 'country_id']);
            $table->unique(['lat', 'lng']);
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
