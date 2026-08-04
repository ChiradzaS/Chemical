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
        Schema::create('tbdeliveryItems', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->integer('productId');
            $table->integer('unitId');
            $table->biginteger('deliveryId')->unsigned();
            $table->foreign('deliveryId')->references('id')->on('tbdelivery')->onDelete('cascade');
            $table->integer('quantity');
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
        Schema::dropIfExists('tbdeliveryItems');
    }
};

