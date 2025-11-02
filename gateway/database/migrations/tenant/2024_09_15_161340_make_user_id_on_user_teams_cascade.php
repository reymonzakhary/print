<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_teams', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_teams', function (Blueprint $table) {
            $table->dropForeign(['user_id', 'team_id']);

            $table->foreignId('team_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
