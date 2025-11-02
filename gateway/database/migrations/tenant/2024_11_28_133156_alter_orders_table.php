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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_nr',
                'invoice_date',

                'payment_method',
                'payment_status',
                'payment_reference',
            ]);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_nr')->nullable()->unique();
            $table->timestamp('invoice_date')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->json('payment_reference')->nullable();

            $table->dropColumn(['updated_by', 'created_by']);
        });
    }
};
