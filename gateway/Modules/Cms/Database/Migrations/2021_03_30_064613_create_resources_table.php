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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('base_id')->index()->nullable();
            $table->string('title')->nullable();
            $table->string('long_title')->nullable();
            $table->string('intro_text')->nullable();
            $table->string('description')->nullable();

            $table->string('menu_title')->nullable();
            $table->string('slug')->index()->nullable();
            $table->string('uri')->index()->nullable();

            $table->unsignedBigInteger('resource_id')->index()->nullable();
            $table->string('language', 3)->nullable();
            // content
            $table->jsonb('content')->nullable();
            // content
            $table->integer('sort')->nullable();

            $table->boolean('isfolder')->default(false);

            $table->boolean('published')->default(true);
            $table->boolean('hidden')->default(false);
            $table->boolean('searchable')->default(true);
            $table->boolean('cacheable')->default(true);
            $table->boolean('hide_children_in_tree')->default(false);


            $table->foreignId('created_by')->index()->nullable();
            $table->foreignId('updated_by')->index()->nullable();
            $table->foreignId('deleted_by')->index()->nullable();
            $table->foreignId('published_by')->index()->nullable();

            $table->foreignId('template_id')->index()->nullable();
            $table->foreignId('ctx_id')->index()->nullable();
            $table->foreignId('parent_id')->index()->nullable();
            //
            $table->foreignId('resource_type_id')->index()->nullable();
            $table->foreignId('locked_by')->index()->nullable();
            //
            $table->timestamp('published_on')->nullable();
            //
            $table->timestamps();
            $table->softDeletes();
            /**
             * foreign keys
             */
            $table->foreign('created_by')->references('id')
                ->on('users')
                ->cascadeOnUpdate();
            $table->foreign('locked_by')->references('id')
                ->on('users')
                ->cascadeOnUpdate();
            $table->foreign('updated_by')->references('id')
                ->on('users')
                ->cascadeOnUpdate();
            $table->foreign('deleted_by')->references('id')
                ->on('users')
                ->cascadeOnUpdate();
            $table->foreign('published_by')->references('id')
                ->on('users')
                ->cascadeOnUpdate();
            $table->foreign('template_id')->references('id')
                ->on('templates')
                ->cascadeOnUpdate();
            $table->foreign('ctx_id')->references('id')
                ->on('contexts')
                ->cascadeOnUpdate();
            $table->foreign('parent_id')->references('id')
                ->on('resources')
                ->cascadeOnDelete();
//            $table->foreign('base_id')->references('id')
//                ->on('resources')
//                ->cascadeOnUpdate();
            $table->foreign('resource_id')->references('id')
                ->on('resources')
                ->cascadeOnDelete();
            $table->foreign('resource_type_id')->references('id')
                ->on('resource_types')
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resources');
    }
};
