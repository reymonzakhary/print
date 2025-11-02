<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', '100')->nullable();
            $table->string('invoice_nr')->unique()->index()->nullable();
            $table->string('order_nr')->unique()->index()->nullable();
            $table->unsignedBigInteger('discount_id')->nullable()->index();

            $table->boolean('type')->default(false);

            $table->unsignedBigInteger('st')->default(300);

            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('delivery_multiple')->default(false); // true / false single
            $table->boolean('delivery_pickup')->nullable()->default(false);

            // @todo have to be updated to match all payment providers
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->json('payment_reference')->nullable();
            //
            $table->unsignedBigInteger('shipping_cost')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->string('note')->nullable();

            $table->enum('created_from', ['mgr', 'web', 'api', 'system'])->default('api');
            $table->unsignedBigInteger('ctx_id')->nullable()->index();

            $table->foreign('ctx_id')->references('id')->on('contexts')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users');

            $table->foreign('discount_id')->references('id')->on('discounts');

            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
