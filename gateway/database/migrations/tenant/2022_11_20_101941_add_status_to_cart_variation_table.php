<?php

use App\Foundation\Status\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToCartVariationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_variation', function (Blueprint $table) {
            $table->integer('st')->nullable()->default(Status::PENDING);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_variation', function (Blueprint $table) {
            $table->dropColumn('st');
        });
    }
}
