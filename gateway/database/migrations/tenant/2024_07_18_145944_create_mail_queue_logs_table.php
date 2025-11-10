<?php

use App\Models\Tenant\MailQueue;
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
        Schema::create('mail_queue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MailQueue::class);
            $table->text('message')->nullable();
            $table->string('st')->nullable();
            $table->text('trace')->nullable(); // what class did fire this mail
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_queue_logs');
    }
};
