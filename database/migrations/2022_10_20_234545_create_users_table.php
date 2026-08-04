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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unique('name');
            $table->string('surname');
            $table->string('cellPhone');
            $table->integer('telephone');
            $table->string('userTypeId');
            $table->string('emailAddress');
            $table->string('userPosition');
            $table->string('userName');
            $table->string('password');
            $table->string('securityLevel');
            $table->string('other');
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
        Schema::dropIfExists('users');
    }
};