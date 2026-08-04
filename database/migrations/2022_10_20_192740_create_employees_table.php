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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unique('name');
            $table->string('surname');
            $table->date('dateOfBirth');
            $table->date('startOfJob');
            $table->string('nationality');
            $table->string('identityNo');
            $table->string('initials');
            $table->string('nickName');
            $table->string('uniqueIdentifiableName');
            $table->integer('documentNo');
            $table->string('documentType');
            $table->string('documentTypeId');
            $table->string('healthComments');
            $table->string('postalAddress');
            $table->string('contactNo');
            $table->integer('cellPhoneNo');
            $table->string('gender');
            $table->string('physicalAddress');
            $table->date('dateOftermination');
            $table->string('other');
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
        Schema::dropIfExists('employees');
    }
};
