<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('allocationItems', function (Blueprint $table) {
            $table->id();
            $table->integer('allocationId');  
            $table->integer('jobCardId');
            $table->integer('productId');
            $table->integer('progress');
            $table->integer('stateId');
            $table->date('start');
            $table->date('end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocationItems');
    }
};
