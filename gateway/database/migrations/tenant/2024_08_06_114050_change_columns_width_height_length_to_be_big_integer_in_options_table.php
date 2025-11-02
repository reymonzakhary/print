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
        Schema::table('options', function (Blueprint $table) {
            $table->unsignedBigInteger('width')->nullable()->change();
            $table->unsignedBigInteger('height')->nullable()->change();
            $table->unsignedBigInteger('length')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            $table->decimal('width', 5, 4)->nullable()->change();
            $table->decimal('height', 5, 4)->nullable()->change();
            $table->decimal('length', 5, 4)->nullable()->change();
        });
    }
};
