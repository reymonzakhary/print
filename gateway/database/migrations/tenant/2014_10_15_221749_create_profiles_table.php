<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->enum('gender', ['female', 'male', 'other'])->nullable();
            $table->string('first_name', '100')->nullable();
//            $table->string('middle_name', '10')->nullable();
            $table->string('last_name', '100')->nullable();
            $table->date('dob')->nullable();
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->json('custom_field')->nullable();
            $table->foreignId('user_id');
            // user created at updated at and deletet at
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
        Schema::dropIfExists('profiles');
    }
}
