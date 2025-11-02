<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_provider_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->unsignedBigInteger('design_provider_template_id')->index();
            $table->string('assets')->nullable();

            $table->foreign('campaign_id')->references('id')
                ->on('campaigns')
                ->cascadeOnDelete();
            $table->foreign('design_provider_template_id')->references('id')
                ->on('design_provider_templates')
                ->cascadeOnDelete();
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
        Schema::dropIfExists('campaign_provider_templates');
    }
};
