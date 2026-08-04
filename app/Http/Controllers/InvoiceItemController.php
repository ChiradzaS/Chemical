<?php

namespace App\Http\Controllers;

use App\Models\Porduct;
use App\Models\StocksTrans;
use App\Models\Stock;
use App\Models\Invoices;
use App\Models\Invoice_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class InvoiceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

   /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create(Request $request)
  {
    $invoicesId = $request->get('invoicesId');
    Log::info(" === Create Invoice Item ......".$invoicesId);      

    return view('invoice_items.create', ['invoicesId' => $invoicesId]);
  }
   


 /**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{

$invoice_item = new Invoice_item;
$invoice_item->invoicesId = $request->invoicesId;
$invoice_item->quantity = $request->quantity;
$invoice_item->unitId = $request->unitId;
$invoice_item->price = $request->price;
$invoice_item->productId = $request->productId;
$invoice_item->stateId = 61;
$invoice_item->userId = Auth::id();
$invoice_item->totalPrice = $request->totalPrice;
$invoice_item->VatType = $request->VatType;
$invoice_item->Discount = $request->Discount;
$invoice_item->vatAmnt= $request->vatAmnt;
$invoice_item->save();

$stocks=DB::table('stocks')->where('productId',$invoice_item->productId )->get();
         


foreach ($stocks as $stock){
   $id = $stock->id;
   $prv = $stock->qnt;

   if($id != null){

      //  echo "<pre>";
      //  print_r( '////////////'.$prv);
      //  exit; 
        $stocktrans = new StocksTrans();
        $stocktrans->stockId = $id;
        $stocktrans->userId = Auth::id();
        $stocktrans->docId= $invoice_item->id;
        $stocktrans->docType= 102;
        $stocktrans->qnt = $invoice_item->quantity;
        $stocktrans->save();

      //    echo "<pre>";
      //  print_r( 'prv,,'.$prv);
      

      //  echo "<pre>";
      //  print_r( 'coming'.$stocktrans->qnt);

      //  echo "<pre>";
      //  print_r( 'coming'.$prv - $stocktrans->qnt );
      //  exit;

       
       


        

        Stock::where('id', $id)
               ->update(['qnt' =>$prv - $stocktrans->qnt,
                         'prvqnt' =>$prv  ]) ;



      }
    }

return redirect()->route('invoices.edit',$invoice_item->invoicesId)->with('success','A new invoice item has been created successfully');

} 

/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\invoice_item   $invoice_item 
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{

$invoice_item  = invoice_item ::find($id);
$invoice_item ->invoicesId = $request->invoicesId;
$invoice_item ->productId = $request->productId;
$invoice_item ->unitId = $request->unitId ;
$invoice_item ->quantity = $request->quantity;
$invoice_item ->price = $request->price;
$invoice_item ->totalPrice = $request->totalPrice;
$invoice_item ->stateId = $request->stateId;
$invoice_item->VatType = $request->VatType;
$invoice_item->Discount = $request->Discount;
$invoice_item->vatAmnt= $request->vatAmnt;
$invoice_item ->save();
return redirect()->route('invoices.edit',$invoice_item->invoicesId)
->with('success','invoice_item  Has Been updated successfully');
}



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
* Show the form for editing the specified resource.
*
* @param  \App\invoice_item  $invoice_item
* @return \Illuminate\Http\Response
*/
public function edit(Invoice_item $invoice_item)
{
  return view('invoice_items.edit',compact('invoice_item'));

}

public function getMessage(Request $request){
  return response()->json(['success' => 'Post created successfully.']);
}  

public function getProductbyidAtItemSelect(Request $request){

    $productid = $request->productid;
  
    $porduct = Porduct::select('*')->where('id', $productid)->get();
   
    // Fetch all records
    $response['data'] = $porduct;
  
    return response()->json($response);
  }


 /**
* Remove the specified resource from storage.
*
* @param  \App\invoice_item  $invoice_item
* @return \Illuminate\Http\Response
*/
public function destroy(Invoice_item $invoice_item)
{
$invoice_item->delete();
return redirect()->route('invoices.edit',$invoice_item->invoicesId)
->with('success','A new invoice item has been deleted successfully');
}


}
