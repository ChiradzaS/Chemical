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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unique('name');
            $table->string('customerType');
            $table->string('accountNumber');
            $table->string('pOAttentionTo');
            $table->string('pOAddressLine1');
            $table->string('pOAddressLine2');
            $table->string('pOAddressLine3');
            $table->string('pOAddressLine4');
            $table->string('pOCity');
            $table->string('pORegion');
            $table->string('pOPostalCode');
            $table->string('pOCountry');
            $table->string('sAAttentionTo');
            $table->string('sAAttentionLine1');
            $table->string('sAAttentionLine2');
            $table->string('sAAttentionLine3');
            $table->string('sAAttentionLine4');
            $table->string('sACity');
            $table->string('sARegion');
            $table->string('sAPostalCode');
            $table->string('sACountry');
            $table->string('emailAddress');
            $table->string('contactNo');
            $table->string('phoneNumber');
            $table->string('faxNumber');
            $table->string('mobileNumber');
            $table->string('dDINumber');
            $table->string('skypeName');
            $table->string('contactPerson');
            $table->string('contactPersonLastName');
            $table->string('vatNo');
            $table->string('bankAccountName');
            $table->string('bankAccountNumber');
            $table->string('bankAccountParticulars');
            $table->string('website');
            $table->string('otherinfo');
            $table->string('dateCreated');
            $table->string('accountsRecevablesTaxCodeName');
            $table->string('accountsPayableTaxCodeName');
            $table->string('legalName');
            $table->string('discount');
            $table->string('companyNumber');
            $table->string('dueDateBillDay');
            $table->string('dueDateBillTerm');
            $table->string('dueDateSalesDay');
            $table->string('dueDateSalesTerm');
            $table->string('salesAccount');
            $table->string('purchaseAccount');
            $table->string('trackingName1');
            $table->string('salesTrackingOption1');
            $table->string('purchasesTrackingOption1');
            $table->string('trackingName2');
            $table->string('salesTrackingOption2');
            $table->string('purchasesTrackingOption2');
            $table->string('brandingTheme');
            $table->string('defaultTaxBills');
            $table->string('defaultTaxSales');
            $table->string('person1FirstName');
            $table->string('person1SecondName');
            $table->string('person1Email');
            $table->string('person2FirstName');
            $table->string('person2SecondName');
            $table->string('person2Email');
            $table->string('person3FirstName');
            $table->string('person3SecondName');
            $table->string('person3Email');
            $table->string('person4FirstName');
            $table->string('person4SecondName');
            $table->string('person4Email');
            $table->string('person5FirstName');
            $table->string('person5SecondName');
            $table->string('person5Email');
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
        Schema::dropIfExists('customers');
    }
};