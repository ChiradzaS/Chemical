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
        Schema::create('porducts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unique('name');
            $table->integer('productTypeId');
            $table->string('description');
            $table->integer('unitTypeId');
            $table->string('WeightPerProduct');
            $table->string('color');
            $table->string('label');
            $table->string('user');
            $table->string('otherInfo');
            $table->integer('printId');
            $table->string('code');
            $table->string('barcode');
            $table->integer('materialTypeId');
            $table->decimal('product_length');
            $table->decimal('product_Width');
            $table->decimal('gussetWidth');
            $table->decimal('totalWidth');
            $table->decimal('thickness');
            $table->decimal('defaultSellingPricePerKg');
            $table->decimal('actualtSellingPricePerKg');
            $table->decimal('defaultSellingPice');
            $table->decimal('actualSellingPrice');
            $table->string('invDescription');
            $table->decimal('costPrice');
            $table->decimal('costPricePerKg');
            $table->decimal('costDefaultPricePerKg');
            $table->decimal('minWeight');
            $table->decimal('maxWeight');
            $table->decimal('avgWorkingWeight');
            $table->decimal('percentMinWeight');
            $table->decimal('percentMaxWeight');
            $table->decimal('perecentAvgWeight');
            $table->decimal('weightPerProductionUnitTypeId');
            $table->decimal('weightPerProductionType');
            $table->decimal('avgWeightPerProduct');
            $table->decimal('minWeightPerProduct');
            $table->decimal('percentMinWeightPerProduct');
            $table->decimal('percentMaxWeightPerProduct');
            $table->decimal('percentWeightPerProduct');
            $table->string('tms');
            $table->tinyInteger('purchasing');
            $table->tinyInteger('invoicing');
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
        Schema::dropIfExists('porducts');
    }
};