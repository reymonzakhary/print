<?php

use App\Enums\PluginStatus;
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
        // Ensure this runs on system database
        Schema::connection('system')->create('plugin_webhook_events', function (Blueprint $table) {
            $table->id();

            // Tenant identification
            $table->unsignedBigInteger('hostname_id');
            $table->foreign('hostname_id')->references('id')->on('hostnames')->onDelete('cascade');

            // Model identification (within tenant context)
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();

            // Event details
            $table->string('event_type');
            $table->json('payload');
            $table->json('plugin_config');

            // Processing status using enum
            $table->string('status', 100)->default(PluginStatus::PENDING->value);
            $table->integer('attempts')->default(0);

            // Response and error tracking
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();

            // Timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['hostname_id', 'status']);
            $table->index(['model_type', 'model_id']);
            $table->index(['status', 'attempts']);
            $table->index(['created_at']);
            $table->index(['hostname_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_webhook_events');
    }
};
