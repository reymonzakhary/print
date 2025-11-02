<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAdminColumnToRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('role_user', 'admin') && Schema::hasColumn('role_user', 'authorizer')) {
            Schema::table('role_user', function (Blueprint $table) {
                $table->dropColumn('admin');
                $table->dropColumn('authorizer');
            });
        }

    }
}
