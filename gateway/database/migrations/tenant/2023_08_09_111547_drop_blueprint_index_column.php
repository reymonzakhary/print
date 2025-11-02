<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DropBlueprintIndexColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('blueprints');
            collect(array_keys($indexesFound))->each(function($c) use ($table) {
                if($c === 'blueprints_blueprint_index') {
                    $table->dropIndex('blueprints_blueprint_index');
                }

                if($c === 'blueprints_configuration_index') {
                    $table->dropIndex('blueprints_configuration_index');
                }
            });
        });
    }
}
