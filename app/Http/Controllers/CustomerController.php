<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Porduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Auth;
use Exception;

class CustomerController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(Request $request)
{

  $action = $request->get('action');

  if( $action <> null && trim($action, ' ') == 'query'){

    $customerId = $request->get('customerId');
    $customerType  = $request->get('customerType');

  
   
  
    $customerIdComp = '<>';
    if ($customerId > 0) {
     
      $customerIdComp = '=';
    } 

    $customerTypeComp = '<>';
    if ($customerType > 0) {
     
      $customerTypeComp = '=';
    } 
  
      
  
    
  $info['customers'] = Customer::  where('id',''.$customerIdComp,$customerId )
                                  ->where('customerType',''.$customerTypeComp,$customerType)                             
                                  ->orderBy('id','asc')->paginate(500);
  
    return  view('customers.index ', $info,['customerId'=> -9,'customerType' => -9, ]);
    }



  $data['customers'] = Customer::orderBy('id','asc')->paginate(500);
  return view('customers.index', $data,['customerId'=> -9,'customerType' => -9, ]);
}
/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
return view('customers.create');
}
/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
    $validated = $request->validate([
        'name'           => 'required|string|max:255',
        'customerType'   => 'required',
        'vatNo'          => 'required|string|max:50',
        'pOAddressLine1' => 'required|string|max:191',
        'pOCity'         => 'required|string|max:191',
        'pORegion'       => 'required|string|max:191',
        'pOPostalCode'   => 'required|string|max:20',
        'pOCountry'      => 'required|string|max:191',
    ], [
        'pOAddressLine1.required' => 'A postal address is required',
        'pOCity.required'         => 'City is required',
        'vatNo.required'          => 'VAT number is required',
    ]);

    $customer = new Customer;

    $customer->name           = $validated['name'];
    $customer->customerType   = $validated['customerType'];
    $customer->vatNo          = $validated['vatNo'];
    $customer->pOAddressLine1 = $validated['pOAddressLine1'];
    $customer->pOCity         = $validated['pOCity'];
    $customer->pORegion       = $validated['pORegion'];
    $customer->pOPostalCode   = $validated['pOPostalCode'];
    $customer->pOCountry      = $validated['pOCountry'];

    $customer->save();



  return redirect()->route('customers.index')->with('success','Company has been created successfully.');



}
/**
* Display the specified resource.
*
* @param  \App\customer  $customer
* @return \Illuminate\Http\Response
*/
public function show(Customer $customer)
{
return view('customers.show',compact('customer'));
} 
/**
* Show the form for editing the specified resource.
*
* @param  \App\Customer  $customer
* @return \Illuminate\Http\Response
*/
public function edit(Customer $customer)
{
return view('customers.edit',compact('customer'));
}
/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\customer  $customer
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$request->validate([
'name' => 'required',
]);
$customer = Customer::find($id);
$customer->name = $request->name;
$customer->customerType = $request->customerType;
$customer->accountNumber = $request->accountNumber;
$customer->pOAttentionTo = $request->pOAttentionTo;
$customer->pOAddressLine1 = $request->pOAddressLine1;
$customer->pOAddressLine2 = $request->pOAddressLine2;
$customer->pOAddressLine3 = $request->pOAddressLine3;
$customer->pOAddressLine4 = $request->pOAddressLine4;
$customer->pOCity = $request->pOCity;
$customer->pORegion = $request->pORegion;
$customer->pOPostalCode = $request->pOPostalCode;
$customer->pOCountry = $request->pOCountry;
$customer->sAAttentionTo= $request->sAAttentionTo;
$customer->sAAttentionLine1 = $request->sAAttentionLine1;
$customer->sAAttentionLine2 = $request->sAAttentionLine2;
$customer->sAAttentionLine3 = $request->sAAttentionLine3;
$customer->sAAttentionLine4 = $request->sAAttentionLine4;
$customer->sACity = $request->sACity;
$customer->sARegion = $request->sARegion;
$customer->sAPostalCode = $request->sAPostalCode;
$customer->sACountry = $request->sACountry;
$customer->emailAddress = $request->emailAddress;
$customer->contactNo = $request->contactNo;
$customer->phoneNumber = $request->phoneNumber;
$customer->faxNumber = $request->faxNumber;
$customer->mobileNumber = $request->mobileNumber;
$customer->dDINumber = $request->dDINumber;
$customer->skypeName = $request->skypeName;
$customer->contactPerson = $request->contactPerson;
$customer->contactPersonLastName = $request->contactPersonLastName;
$customer->vatNo = $request->vatNo;
$customer->bankAccountName = $request->bankAccountName;
$customer->bankAccountNumber = $request->bankAccountNumber;
$customer->bankAccountParticulars = $request->bankAccountParticulars;
$customer->website = $request->website;
$customer->otherinfo = $request->otherinfo;
$customer->dateCreated = $request->dateCreated;
$customer->accountsRecevablesTaxCodeName = $request->accountsRecevablesTaxCodeName;
$customer->accountsPayableTaxCodeName = $request->accountsPayableTaxCodeName;
$customer->legalName = $request->legalName;
$customer->discount = $request->discount;
$customer->companyNumber = $request->companyNumber;
$customer->dueDateBillDay = $request->dueDateBillDay;
$customer->dueDateBillTerm = $request->dueDateBillTerm;
$customer->dueDateSalesDay = $request->dueDateSalesDay;
$customer->dueDateSalesTerm = $request->dueDateSalesTerm;
$customer->salesAccount = $request->salesAccount;
$customer->purchaseAccount = $request->purchaseAccount;
$customer->trackingName1 = $request->trackingName1;
$customer->salesTrackingOption1 = $request->salesTrackingOption1;
$customer->trackingName2 = $request->trackingName2;
$customer->salesTrackingOption2 = $request->salesTrackingOption2;
$customer->purchasesTrackingOption2 = $request->purchasesTrackingOption2;
$customer->brandingTheme = $request->brandingTheme;
$customer->defaultTaxBills = $request->defaultTaxBills;
$customer->defaultTaxSales = $request->defaultTaxSales;
$customer->person1FirstName = $request->person1FirstName;
$customer->person1SecondName = $request->person1SecondName;
$customer->person1Email = $request->person1Email;
$customer->person2FirstName = $request->person2FirstName;
$customer->person2SecondName = $request->erson2SecondName;
$customer->person2Email = $request->person2Email;
$customer->person3FirstName = $request->person3FirstName;
$customer->person3SecondName = $request->person3SecondName;
$customer->person3Email = $request->erson3Email;
$customer->person4FirstName = $request->person4FirstName;
$customer->person4SecondName = $request->person4SecondName;
$customer->person4Email = $request->person4Email;
$customer->person5FirstName = $request->erson5FirstName;
$customer->person5SecondName = $request->person5SecondName;
$customer->person5Email = $request->person5Email;
$customer->save();
return redirect()->route('customers.index')
->with('success','Customer Has Been updated successfully');
}
/**
* Remove the specified resource from storage.
*
* @param  \App\Customer  $customer
* @return \Illuminate\Http\Response
*/
public function destroy(Customer $customer)
{
$customer->delete();
return redirect()->route('customers.index')
->with('success','Customer has been deleted successfully');
}

public function search(Request $request)
{
    $searchTerm = $request->input('searchTerm');
    $products = Porduct::where('name', 'like', '%' . $searchTerm . '%')->get();

    return view('search.results', compact('products'));
}

}
