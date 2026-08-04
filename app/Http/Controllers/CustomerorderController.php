<?php
namespace App\Http\Controllers;
use App\Models\Customerorder;
use App\Models\Customercustomerordercustomeritem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class CustomerorderController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/

public function index()
{
  $data['ordercustomers'] = Customerorder::orderBy('id','asc')->paginate(50);
  return view('ordercustomers.index', $data);
}




/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
  
return view('ordercustomers.create');
}



/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
$ordercustomer = new Customerorder;
$ordercustomer->reference = $request->reference;
$ordercustomer->date = $request->date;
$ordercustomer->other = $request->other;
$ordercustomer->customerId = $request->customerId;
$ordercustomer->totalValue = $request->totalValue;
$ordercustomer->userId = Auth::id();
$ordercustomer->stateId = 0;
$ordercustomer->save();

$customerorderitem = new Customercustomerordercustomeritem;
$customerorderitem->ordercustomersId = $ordercustomer->id;
$customerorderitem->quantity = $request->quantity;
$customerorderitem->other = $request->other;
$customerorderitem->userId = Auth::id();
$customerorderitem->unitId = $request->unitId;
$customerorderitem->price = $request->price;
$customerorderitem->productId = $request->productId;
$customerorderitem->stateId = $request->stateId;
$customerorderitem->totalPrice = $request->totalPrice;
$customerorderitem->save();
  return redirect()->route('ordercustomers.index')->with('success','ordercustomers has been created successfully.');
}





/**
* Display the specified resource.
*
* @param  \App\ordercustomer  $ordercustomer
* @return \Illuminate\Http\Response
*/
public function show(Customerorder $customerorder,Request $request)
{
  $customerorderitems=DB::table('customercustomerordercustomeritems')->where('ordercustomersId', $ordercustomer->id)->get();
  
   View::share('ordercustomer', $ordercustomer);
   View::share('customerordercustomeritems',$customerordercustomeritems);
 
return view('ordercustomers.show',compact('ordercustomer'));
} 





/**
* Show the form for editing the specified resource.
*
* @param  \App\ordercustomers  $ordercustomer
* @return \Illuminate\Http\Response
*/
public function edit(Customerorder $ordercustomer,Request $request)
{
  $customerorderitems=DB::table('customerorderitems')->where('ordersId', $ordercustomer->id)->get();
  
   View::share('ordercustomer', $ordercustomer);
   View::share('customerorderitems',$customerorderitems);

    //   echo "<pre>";
    // print_r($customerordercustomeritems);
    //  exit;

    // foreach ($customerordercustomeritems as $customerordercustomeritem){

    //   $jobcarditems=DB::table('jobcarditems')->where('jobCardId', $customerordercustomeritem->job_card_id)->get();
    // }
    // //     echo "<pre>";
    // // print_r( $jobcarditems);
    // //  exit;
    // foreach ($jobcarditems as $jobcarditem){
    //   $jobCardItemId = $jobcarditem->id;
    //   $productId = $jobcarditem->productId;

    //   $outstanding = DB::table('jobcarditems')
    //   ->selectRaw("qnt - (select  sum(qnt) as qnt from `productionitems` where jobcardItemId  = '".$jobCardItemId."' ) as outstanding")
    //   ->where('id', $jobCardItemId)
    //   ->where('productId', $productId)
    //   ->value('outstanding');
  
    //   $jobcarditem->outstanding = $outstanding;

    //   $customerordercustomeritemitem->outstanding = $jobcarditem->outstanding;
    

       



    // }

   

return view('ordercustomers.edit',compact('ordercustomer'));
}




/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\ordercustomer  $ordercustomer
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$request->validate([
'reference' => 'required',

]);
$ordercustomer = Customerorder::find($id);
$ordercustomer->reference = $request->reference;
$ordercustomer->date = $request->date;
$ordercustomer->other = $request->other;
$ordercustomer->customerId = $request->customerId;
$ordercustomer->totalValue = $request->totalValue;
$ordercustomer->stateId = $request->stateId;
$ordercustomer->save();
return redirect()->route('ordercustomers.index')
->with('success','ordercustomers Has Been updated successfully');
}


/**
* Remove the specified resource from storage.
*
* @param  \App\ordercustomers  $ordercustomer
* @return \Illuminate\Http\Response
*/
public function destroy(Customerorder $customerorder)
{
$ordercustomer->delete();
return redirect()->route('ordercustomers.index')
->with('success','ordercustomers has been deleted successfully');
}



}
