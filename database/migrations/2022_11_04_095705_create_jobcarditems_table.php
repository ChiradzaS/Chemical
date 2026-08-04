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
        Schema::create('jobcarditems', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->string('name');
            $table->unique('name');
            $table->biginteger('jobCardId')->unsigned();
            $table->foreign('jobCardId')->references('id')->on('job_cards')->onDelete('cascade');
            $table->integer('processId');
            $table->integer('productId');
            $table->decimal('qnt');
            $table->integer('unitId');
            $table->string('barcode');
            $table->unique('barcode');
            $table->string('other');
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
        Schema::dropIfExists('jobcarditems');
    }
};

