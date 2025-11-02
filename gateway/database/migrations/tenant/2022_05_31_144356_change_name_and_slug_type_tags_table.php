<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNameAndSlugTypeTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('name', 50)->change();
            $table->string('slug', 50)->change();
            $table->integer('row_id')->nullable();
            $table->string('iso')->nullable();
            $table->string('hex', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('slug')->change();
            $table->dropColumn('row_id');
            $table->dropColumn('iso');
            $table->dropColumn('hex');
        });
    }
}
