<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAddressableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addressable', function (Blueprint $table) {
            $table->string('company_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('tax_nr')->nullable();
            $table->string('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addressable', function (Blueprint $table) {
            $table->dropColumn('company_name');
            $table->dropColumn('full_name');
            $table->dropColumn('tax_nr');
            $table->dropColumn('phone_number');
        });
    }
}
