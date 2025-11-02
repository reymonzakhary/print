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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('invoice_nr')->unique()->nullable();
            $table->timestamp('invoice_date')->nullable();

            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('st')->nullable();

            $table->decimal('fee')->nullable();
            $table->decimal('vat')->nullable();

            $table->foreignId('discount_id')
                ->nullable()
                ->constrained('discounts')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->unsignedBigInteger('price')->nullable();
            $table->jsonb('custom_field')->nullable();

            $table->unsignedBigInteger('company_id')->nullable();

            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('contract_id')->nullable();

            $table->string('type');

            $table->unsignedBigInteger('counter')->nullable();
            $table->unsignedBigInteger('level')->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('transactions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamp('due_date')->nullable();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
