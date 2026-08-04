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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unique('name');
            $table->biginteger('productId')->unsigned();
            $table->foreign('productId')->references('id')->on('porducts')->onDelete('cascade');
            $table->integer('outerPackagePerProductId');
            $table->integer('minWeight');
            $table->integer('avgWeight');
            $table->integer('maxWeight');
            $table->integer('printLabel');
            $table->integer('prnBarcode');
            $table->integer('prnSerialNo');
            $table->integer('barcode');
            $table->integer('custBarcode');
            $table->integer('unitTypeId');
            $table->integer('labelLine1');
            $table->integer('labelLine2');
            $table->integer('labelLine3');
            $table->integer('labelLine4');
            $table->integer('otherInfo');
            $table->integer('otherPackagingDetails');
            $table->integer('ratioToProduct');
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
        Schema::dropIfExists('packages');
    }
};
