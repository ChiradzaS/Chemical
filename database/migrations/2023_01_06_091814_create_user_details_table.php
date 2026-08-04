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
        Schema::create('user_details', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('name');
            $table->string('surname');
            $table->string('cellPhone');
            $table->string('telephone')->nullable();
            $table->integer('userTypeId');
            $table->integer('userId')->nullable();
            $table->integer('orderId')->nullable();
            $table->string('emailAddress');
            $table->string('userPosition');
            $table->string('userName')->unique();
            $table->string('password');
            $table->integer('securityLevel');
            $table->string('other')->nullable();
            $table->integer('userTypeById')->nullable();
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
        Schema::dropIfExists('user_details');
    }
};
