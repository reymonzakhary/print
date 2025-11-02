<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnOrderIdToVatsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vats', function (Blueprint $table) {
            $table->integer('vatable_id');
            $table->string('vatable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vats', function (Blueprint $table) {
            $table->dropColumn('vatable_id');
            $table->dropColumn('vatable_type');
        });
    }
}
