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
        Schema::create('machineries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unique('name');
            $table->string('refNo');
            $table->string('description');
            $table->string('serialNo');
            $table->string('machineryTypeId');
            $table->string('addressOfMachine');
            $table->string('other');
            $table->string('bookValue');
            $table->string('realisticValue');
            $table->string('startDate');
            $table->string('endDate');
            $table->string('manufactureOfMachine');
            $table->string('emailAddressManufacturer');
            $table->string('websiteManufacturer');
            $table->string('contactPersonOfManufacture');
            $table->string('contactDetailsOfManufacture');
            $table->string('addressOfManufacturer');
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
        Schema::dropIfExists('machineries');
    }
};