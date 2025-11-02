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
        Schema::table('mail_queues', function (Blueprint $table) {
            $table->string('sent_at')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('subject')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_queues', function (Blueprint $table) {
            $table->dropColumn('sent_at', 'from', 'to', 'subject', 'cc', 'bcc');
        });
    }
};
