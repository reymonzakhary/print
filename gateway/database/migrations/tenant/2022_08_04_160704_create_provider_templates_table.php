<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
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
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_templates');
    }
}
