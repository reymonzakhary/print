<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDesignProviderTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('design_provider_templates', function (Blueprint $table) {
            $table->string('folder')->nullable();
            $table->string('icon')->nullable();
            $table->string('type')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('locked')->default(0);
            $table->foreignId('locked_by')->nullable()->constrained('users');
            $table->json('properties')->nullable();
            $table->boolean('static')->default(0);
            $table->string('path')->nullable();
            $table->integer('sort')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('design_provider_templates', function (Blueprint $table) {
            $table->dropColumn('folder');
            $table->dropColumn('icon');
            $table->dropColumn('type');
            $table->dropColumn('content');
            $table->dropColumn('locked');
            $table->dropColumn('locked_by');
            $table->dropColumn('properties');
            $table->dropColumn('static');
            $table->dropColumn('path');
            $table->dropColumn('sort');
            $table->dropColumn('created_by');
        });
    }
}
