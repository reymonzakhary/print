<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds tenant_id to Passport tables for Universal Mode.
     * This allows all tenants to use the same OAuth clients from the central database,
     * with tokens scoped by tenant_id.
     */
    public function up(): void
    {
        // Add tenant_id to oauth_access_tokens
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->index()->after('user_id');
        });

        // Add tenant_id to oauth_auth_codes
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->index()->after('user_id');
        });

        // Add tenant_id to oauth_refresh_tokens (if you want to scope refresh tokens)
        // Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
        //     $table->string('tenant_id')->nullable()->index()->after('access_token_id');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        // Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
        //     $table->dropColumn('tenant_id');
        // });
    }
};
