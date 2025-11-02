<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDiskFromMediaSourceRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media_source_rules', function (Blueprint $table) {
            $table->string('disk')->default('tenancy')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_source_rules', function (Blueprint $table) {
            $table->string('disk')->nullable()->change();
        });
    }
}
