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
        Schema::table('addressable', function (Blueprint $table) {
            $table->integer('dial_code')->default(31);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addressable', function (Blueprint $table) {
            $table->dropColumn('dial_code');
        });
    }
};
