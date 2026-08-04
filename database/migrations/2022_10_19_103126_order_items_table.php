

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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->biginteger('ordersId')->unsigned();
            $table->foreign('ordersId')->references('id')->on('orders')->onDelete('cascade');
            $table->string('produictId');
            $table->string('unitId');
            $table->string('quantity');
            $table->string('other');
            $table->string('price');
            $table->string('status');
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
        Schema::dropIfExists('order_items');
    }
};
