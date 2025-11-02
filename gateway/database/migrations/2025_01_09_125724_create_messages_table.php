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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->foreignId('parent_id')->nullable();

            $table->string('to')->default('cec')->nullable();
            $table->string('type')->default('producer')->nullable();

            $table->foreignId('contract_id')->nullable();

            $table->foreignId('sender_hostname')->nullable();
            $table->foreignId('recipient_hostname')->nullable();

            $table->string('sender_name');
            $table->string('sender_email');

            $table->string('recipient_email')->nullable();

            $table->boolean('read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
