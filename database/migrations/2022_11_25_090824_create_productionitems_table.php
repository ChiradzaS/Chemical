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
        Schema::create('productionitems', function (Blueprint $table) {
            $table->id();
            $table->biginteger('productionId')->unsigned();
            $table->foreign('productionId')->references('id')->on('productions')->onDelete('cascade');
            $table->string('jobcarditemId');
            $table->string('other');
            $table->string('mnfTime');
            $table->string('productId');
            $table->string('qnt');
            $table->string('qntUnitId');
            $table->string('username');
            $table->string('processId');
            $table->string('machineryId');
            $table->string('employeeId');
            $table->string('productionDate');
            $table->string('shiftId');
            $table->string('serialNo');
            $table->string('weight');
            $table->string('state');
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
        Schema::dropIfExists('productionitems');
    }
};