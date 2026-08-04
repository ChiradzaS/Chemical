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
        Schema::create('job_cards', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->string('refNo');
            $table->unique('refNo');
            $table->string('description')->default(' ');
            $table->date('startDate');
            $table->integer('productId');
            $table->string('other')->default(' ');
            $table->string('noOfProcesses');
            $table->decimal('qnt');
            $table->integer('unitId');
            $table->integer('customerId');
            $table->integer('stateId');
            $table->integer('userId');
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
        Schema::dropIfExists('job_cards');
    }
};
