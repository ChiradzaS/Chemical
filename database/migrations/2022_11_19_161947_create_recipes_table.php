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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unique('name');
            $table->biginteger('productId')->unsigned();
            $table->foreign('productId')->references('id')->on('porducts')->onDelete('cascade');
            $table->integer('reference');
            $table->integer('productAllocationId');
            $table->integer('enable');
            $table->integer('allocationTypeId');
            $table->integer('quantityAllocation');
            $table->integer('qntUnitId');
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
        Schema::dropIfExists('recipes');
    }
};
