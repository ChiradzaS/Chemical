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
        Schema::create('set_prices', function (Blueprint $table) {


            $table->increments('id')->unique();
            $table->integer('name')->nullable();
            $table->integer('customerId')->nullable();
            $table->integer('width')->nullable();
            $table->integer('gusset')->nullable();
            $table->integer('totalWidth')->nullable();
            $table->integer('length')->nullable();
            $table->integer('micron')->nullable();
            $table->integer('actualMicron')->nullable();
            $table->integer('material')->nullable();
            $table->integer('colourId')->nullable();
            $table->integer('bagType')->unique();
            $table->float('pricePerKg')->nullable();
            $table->float('pricePer1000')->nullable();
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
        Schema::dropIfExists('set_prices');
    }
};
