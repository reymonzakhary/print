<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSubTenantIdToHostnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hostnames', function (Blueprint $table) {
            $table->uuid('host_id')->index()->nullable();
            $table->boolean('primary')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hostnames', function (Blueprint $table) {
            $table->dropColumn('host_id');
            $table->dropColumn('primary');
        });
    }
}
