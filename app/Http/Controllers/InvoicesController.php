<?php

namespace App\Http\Controllers;

use App\Models\Porduct;
use App\Models\Invoices;
use App\Models\Invoice_item;
use App\Models\StocksTrans;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class InvoicesController extends Controller
{
    //
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



  
$customerComp = '<>';
if ($customerId > 0) {
   $customerComp = '=';
}



   

  $data['invoices'] = Invoices::where('customerId',''.$customerComp,$customerId)                             
                                ->orderBy('updated_at','desc')->paginate(500);

return  view('invoices.index', $data);
}
        $data['invoices'] = Invoices::orderBy('updated_at','desc')->paginate(500);
        return view('invoices.index', $data);
        return  view('invoices.index');

 
    }

    /** 
    * Show the form for editing the specified resource.
    *
    * @param  \App\Recipe  $recipe
    * @return \Illuminate\Http\Response
    */
    public function create(Request $request)
    {
        $productId = $request->get('productId');
       // Log::info(" getting id from model ------------------------------------------- : ".$productId); 
        
        $porduct = Porduct::find($productId);
 // Log::info(" - 1 Update Job Card ------------------------------------------- : ".$porduct); 
    
        View::share('porduct',$porduct);
     
        return view('invoices.create', ['productId' => $productId]);
     
    }


/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\invoice  $invoice
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$request->validate([
'reference' => 'required',

]);
$invoice = Invoices::find($id);
$invoice->reference = $request->reference;
$invoice->other = $request->other;
$invoice->customerId = $request->customerId;
$invoice->totalValue = $request->totalValue;
$invoice->stateId = $request->stateId;
$invoice->save();
return redirect()->route('invoices.index')
->with('success','Invoices has been updated successfully');
}


/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
$invoice = new Invoices;
$invoice->reference = $request->reference;
$invoice->other = $request->other;
$invoice->customerId = $request->customerId;
$invoice->totalValue = $request->totalValue;
$invoice->stateId = 0;
$invoice->save();



$invoiceitem = new Invoice_item;
$invoiceitem->invoicesId = $invoice->id;
$invoiceitem->quantity = $request->quantity;
$invoiceitem->unitId = $request->unitId;
$invoiceitem->price = $request->price;
$invoiceitem->productId = $request->productId;
$invoiceitem->stateId = 0;
$invoiceitem->totalPrice = $request->totalPrice;
$invoiceitem->VatType = $request->VatType;
$invoiceitem->Discount = $request->Discount;
$invoiceitem->vatAmnt= $request->vatAmnt;
$invoiceitem->save();


// $stocks=DB::table('stocks')->where('productId',$invoiceitem->productId )->get();
         


// foreach ($stocks as $stock){
//    $id = $stock->id;
//    $prv = $stock->qnt;

//    if($id != null){

//       //  echo "<pre>";
//       //  print_r( '////////////'.$prv);
//       //  exit; 
//         $stocktrans = new StocksTrans();
//         $stocktrans->stockId = $id;
//         $stocktrans->userId = Auth::id();
//         $stocktrans->docId= $invoiceitem->id;
//         $stocktrans->docType= 102;
//         $stocktrans->qnt = $invoiceitem->quantity;
//         $stocktrans->save();



//           Stock::where('id', $id)
//                ->update(['qnt' =>$prv - $stocktrans->qnt,
//                          'prvqnt' =>$prv  ]) ;

//       }
//     }

  return redirect()->route('invoices.index')->with('success','Invoice has been created successfully.');
}


/**
* Show the form for editing the specified resource.
*
* @param  \App\Invoices  $invoice
* @return \Illuminate\Http\Response
*/
public function edit(Invoices $invoice)
{
  $invoiceitems=DB::table('invoice_items')->where('invoicesId', $invoice->id)->get();

  $total = DB::table('invoice_items')->where('invoicesId', $invoice->id)
            ->select(DB::raw('SUM(totalPrice) as total'))
            ->value('total');
  
  if($total==null){
    $total=0.00;
  }

  $totalVat = DB::table('invoice_items')->where('invoicesId', $invoice->id)
            ->select(DB::raw('SUM(vatAmnt) as total'))
            ->value('total');

  $totalexclVAT =  $total - $totalVat;


          
        
  
   View::share('invoice', $invoice);
   View::share('total', $total);
   View::share('totalVat',  $totalVat);
   View::share('totalexclVAT', $totalexclVAT);
   View::share('invoiceitems',$invoiceitems);
   Log::info("Update invoices ------------------------------------------- : ".$invoice->id); 
   Log::info("Update invoices ------------------------------------------- : ".$invoiceitems); 

return view('invoices.edit',compact('invoice'));
}


/**
* Remove the specified resource from storage.
*
* @param  \App\Invoices  $order
* @return \Illuminate\Http\Response
*/
public function destroy(Invoices $invoice)
{
$invoice->delete();
return redirect()->route('invoices.index')
->with('success','Invoice has been deleted successfully');
}


}
