<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierHostsnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_hostsnames', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('hostname_id');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')
                ->on('suppliers')->cascadeOnDelete()->onUpdate('cascade');
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
        Schema::dropIfExists('supplier_hostsnames');
    }
}
