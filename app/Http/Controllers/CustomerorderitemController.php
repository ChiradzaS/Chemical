<?php
namespace App\Http\Controllers;
use App\Models\Customerorderitem;
use Illuminate\Http\Request;
use App\Models\Porduct;
use App\Models\Oder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;





class CustomerorderitemController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/

public function index()
{
  $data['customerorderitems'] = Customerorderitem::orderBy('id','asc')->paginate(50);
  return view('customerorderitems.index', $data);
}



/**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create(Request $request)
  {
    $ordersId = $request->get('ordersId');   
    return view('customerorderitems.create',['ordersId' => $ordersId]);
  }



/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{

$customerorderitem = new Customerorderitem;
$customerorderitem->ordersId = $request->ordersId;
$customerorderitem->quantity = $request->quantity;
$customerorderitem->other = $request->other;
$customerorderitem->unitId = $request->unitId;
$customerorderitem->price = $request->price;
$customerorderitem->productId = $request->productId;
$customerorderitem->userId = Auth::id();
$customerorderitem->stateId = $request->stateId;
$customerorderitem->price = $request->price;
$customerorderitem->save();
return redirect()->route('ordercustomers.edit',$customerorderitem->ordersId)->with('success','A new order iterm Has Been created successfully');
}





/**
* Display the specified resource.
*
* @param  \App\customerorderitem  $customerorderitem
* @return \Illuminate\Http\Response
*/
public function show(Customerorderitem $customerorderitem)
{
  $orderitems=DB::table('customerorderitems')->where('ordersId', $customerorderitem->id)->get();
  
 // View::share('order', $order);
  View::share('orderitems',$orderitems);
  return view('customerorderitems.show',compact('customerorderitem'));

}


/**
* Show the form for editing the specified resource.
*
* @param  \App\customerorderitem  $customerorderitem
* @return \Illuminate\Http\Response
*/
public function edit(Customerorderitem $customerorderitem)
{

  Log::info(" ------ Edit .... ".$customerorderitem->id);  
   return view('customerorderitems.edit',compact('customerorderitem'));
}




/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\customerorderitem  $customerorderitem
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$customerorderitem = Customerorderitem::find($id);
$customerorderitem->ordersId = $request->ordersId;
$customerorderitem->quantity = $request->quantity;
$customerorderitem->other = $request->other;
$customerorderitem->unitId = $request->unitId;
$customerorderitem->price = $request->price;
$customerorderitem->productId = $request->productId;
$customerorderitem->stateId = $request->stateId;
$customerorderitem->totalPrice = $request->totalPrice;
$customerorderitem->save();
return redirect()->route('orders.edit',$customerorderitem->ordersId)
->with('success','A new order iterm Has Been updated successfully');
}


/**
* Remove the specified resource from storage.
*
* @param  \App\customerorderitem  $customerorderitem
* @return \Illuminate\Http\Response
*/
public function destroy(customerorderitem $customerorderitem)
{
$customerorderitem->delete();
return redirect()->route('orders.edit',$customerorderitem->ordersId)
->with('success','A new order iterm Has Been deleted  successfully');
}


public function getProductbyid(Request $request){

  $productid = $request->productid;

  $porduct = Porduct::select('*')->where('id', $productid)->get();
 
  // Fetch all records
  $response['data'] = $porduct;

  return response()->json($response);
}

}