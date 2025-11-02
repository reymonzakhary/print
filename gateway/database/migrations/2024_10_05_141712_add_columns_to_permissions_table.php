<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('namespace')->nullable();
            $table->string('area')->nullable();
            $table->unique(['namespace', 'area', 'name']);
        });

        // Create table for storing teams
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('namespace');
            $table->dropColumn('area');
//            $table->dropUnique(['namespace', 'area', 'name']);
        });

        // Create table for storing teams
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
};
