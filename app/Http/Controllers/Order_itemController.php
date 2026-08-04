<?php
namespace App\Http\Controllers;
use App\Models\Order_item;
use App\Models\JobCard;
use App\Models\Type;
use Illuminate\Http\Request;
use App\Models\Porduct;
use App\Models\Allocation;
use App\Models\Oder;
use App\Models\set;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;
use Exception;





class Order_itemController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/

public function index(Request $request)
{

  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 
  





  $jobcardId = $request->get('jobCardId');
  $productId = $request->get('productId');
  $completelist = $request->get('orders');

  if($completelist){

    $data['order_items'] = Order_item::where('stateId','=', 45)
                                      ->orderBy('customerId','desc')
                                      ->paginate(50);


return view('order_items.index', $data);

  }

  if( $jobcardId > 0){

    $data['order_items'] = Order_item::where('productId', $productId)
                                     ->where('job_card_id','=', 0)
                                     ->orderBy('updated_at','desc')->paginate(1000);
                                      
    return view('order_items.linkitems', $data,['jobcardId' => $jobcardId]);
  }

  $action = $request->get('action');

  
  

 



     if ($action <> null && trim($action, ' ') == 'query'){
  
      $productId = $request->get('productId');
  
      $customerId = $request->get('customerId');

  

      $fromDate = $request->get('fromDate');
      if ($fromDate == null) {
        $fromDate = '2020-12-31';
      }
    
      $toDate = $request->get('toDate');
      if ($toDate == null) {
        $toDate = '2030-12-31';
      }


      $productComp = '<>';
      if ($productId > 0) {
         $productComp = '=';
      }

      $customerComp = '<>';
      if ($customerId > 0) {
         $customerComp = '=';
      }
                

               
                // $data['order_items'] = Order_item:: where('customerId', $customerComp, $customerId)
                //                                     ->whereDate('created_at', '<=', $toDate)
                //                                     ->whereDate('startDate', '>=', $fromDate)
                //                                     ->where('productId', $productComp, $productId)  
                //                                    ->orderBy('updated_at','desc')
                //                                    ->paginate(50);

     $search = 10;
                                                   $data = [

                                                    'search'  =>   $search ,                                                  'customerId' => $customerId,
                                                    'productId' => $productId,
                                                    'toDate' => $toDate,
                                                    'fromDate' => $fromDate,
                                                    'customerComp' => $customerComp,
                                                    'productComp' => $productComp
                                                ];

  
    
    //                                             $response = Http::get("$url/qryorderitems/index?customerId=".$customerId.'&&productId='.$productId.'&&toDate='.$toDate.'&&fromDate='.$fromDate.'&&customerComp='.$customerComp.'&&productComp='.$productComp.'&&search='.$search);
     
     
    // if ($response->successful()) {
       
    //   $data['order_items'] = json_decode($response);

    //  //dd($data);
        
    //     return view('order_items.index', $data);
    
    // } else {
        
    //     dd('Sorry , there an error with your request');
    
    // }


    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Make the HTTP request
          $response = Http::timeout(10) // Set a timeout of 10 seconds
                           ->retry(3, 1000) // Retry 3 times with a 1-second delay
                           ->get("$url/qryorderitems/index?customerId=".$customerId.'&&productId='.$productId.'&&toDate='.$toDate.'&&fromDate='.$fromDate.'&&customerComp='.$customerComp.'&&productComp='.$productComp.'&&search='.$search);
  
          //$data['info'] = json_decode($response, true);
      
          // Check if the request was successful
          if ($response->successful() ){
            
            $data['order_items'] = json_decode($response);

            //dd($data);
               
               return view('order_items.index', $data);
  
          } else {
              // Throw an exception if the request fails
              return view('errorpage', [
                'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
            ]);
  
          }
      } catch (Exception $e) {
          // Log the error
          Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
          return view('errorpage', [
            'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
        ]);
  
          // If this is the last attempt, return an error message
          // if ($attempt === $maxRetries) {
          //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
          // }
  
          if ($attempt === $maxRetries) {
            return view('errorpage', [
                'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
            ]);
        }
  
          // Wait before retrying
          sleep($retryDelay);
      }
  }


     }
     

     
  //    $response = Http::get($url.'/qryorderitems/index');
     
     
  //    if ($response->successful()) {
        
  //      $data['order_items'] = json_decode($response);

      
         
  //        return view('order_items.index', $data);
     
  //    } else {
         
  //        dd('Sorry , there an error with your request');
     
  //    }
  
  // view('order_items.index', $data);


  

  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
                         ->retry(3, 1000) // Retry 3 times with a 1-second delay
                         ->get($url.'/qryorderitems/index');

        //$data['info'] = json_decode($response, true);
    
        // Check if the request was successful
        if ($response->successful() ){

          $data['order_items'] = json_decode($response);
          return view('order_items.index', $data);

        } else {
            // Throw an exception if the request fails
            return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);

        }
    } catch (Exception $e) {
        // Log the error
        Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
        return view('errorpage', [
          'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
      ]);

        // If this is the last attempt, return an error message
        // if ($attempt === $maxRetries) {
        //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
        // }

        if ($attempt === $maxRetries) {
          return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);
      }

        // Wait before retrying
        sleep($retryDelay);
    }
}
}






/**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create(Request $request)
  {

    

    $ordersId = $request->get('ordersId');
    $customerId = $request->get('customer');

    
     

    return view('order_items.create', ['ordersId' => $ordersId , 'customerId' =>  $customerId ]);
  }



/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{

  $itemIds = $request->input('item_ids');
  $jobcardId = $request->get('jobcardId');

   

    //  if( $itemIds > 0){

    //  foreach ($itemIds as $itemId)
    //   {
    //     Order_item::where('id',  $itemId)
    //                ->update(['job_card_id' =>$jobcardId]);

    //   }


    //   $data['order_items'] = Order_item::orderBy('id','asc')->paginate(50);                               
    //   return view('order_items.index',$data)->with('success','Job cards has successfully been linked ');
    // }

// $order_item = new Order_item;
// $order_item->ordersId = $request->ordersId;
// $order_item->quantity = $request->quantity;
// $order_item->other = $request->other;
// $order_item->unitId = $request->unitId;
// $order_item->price = $request->price;
// $order_item->openningQNT = $order_item->quantity;
// $order_item->productId = $request->productId;
// $order_item->reference = $request->reference;
// $order_item->dueDate = $request->dueDate;
// $order_item->stateId = 61;
// $order_item->totalPrice = $request->totalPrice;
// $order_item->customerId = $request->customerId; 
// $order_item->userId = Auth::id();
// $order_item->save();


$data = [
   
 
  'ordersId' => $request->ordersId,
  'reference' => $request->reference,
  'date' => $request->date,
  'order_other' => $request->other,
  'customerId' => $request->customerId,
  'datePlaced' => $request->datePlaced,
  'dueDate' => $request->dueDate,
  'userId' => Auth::id(),
  'quantity' => $request->quantity,
  'order_item_other' => $request->other_item,
  'unitId' => $request->unitId,
  'price' => $request->price,
  'openningQNT' => $request->quantity, 
  'reference' => $request->reference_item, 
  'productId' => $request->productId,
  'totalPrice' => $request->totalPrice,

];

 
$url = env('APP_URL');
$maxRetries = 3; 
$retryDelay = 2; 



//dd($url);

// $response = Http::get($url.'/qryorderitems/store', $data);

// Log::info('cant get '.$response );
// if ($response->successful()) {
 
//   $orderId = $response->json($response); 
  
//   return redirect()->route('orders.index')->with('success','Orders has been created successfully.');

// } else {
  
//   dd('Sorry , there an error with your request');

// }

// return redirect()->route('orders.edit',$order_item->ordersId)->with('success','A new order iterm Has Been created successfully');


//--------------------------------------------------------------------------------------------------------------------------------------------------

for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
  try {
      // Make the HTTP request
      $response = Http::timeout(10) // Set a timeout of 10 seconds
                       ->retry(3, 1000) // Retry 3 times with a 1-second delay
                       ->get($url.'/qryorderitems/store', $data);

      //$data['info'] = json_decode($response, true);
  
      // Check if the request was successful
      if ($response->successful() ){

        $orderId = $response->json($response); 
  
        return redirect()->route('orders.index')->with('success','Orders has been created successfully.');

      } else {
          // Throw an exception if the request fails
          return view('errorpage', [
            'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
        ]);

      }
  } catch (Exception $e) {
      // Log the error
      Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
      return view('errorpage', [
        'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
    ]);

      // If this is the last attempt, return an error message
      // if ($attempt === $maxRetries) {
      //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
      // }

      if ($attempt === $maxRetries) {
        return view('errorpage', [
            'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
        ]);
    }

      // Wait before retrying
      sleep($retryDelay);
  }
}

}

/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function storel(Request $request)
{

}



/**
* Display the specified resource.
*
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function show(Order_item $order_item)
{
  $orderitems=DB::table('order_items')->where('ordersId', $order_item->id)->get();
  
 // View::share('order', $order);
  View::share('orderitems',$orderitems);
  return view('order_items.show',compact('order_item'));

}

/**
* Display the specified resource.
*
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function showl(Order_item $order_item)
{
}

/**
* Show the form for editing the specified resource.
*
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function edit(Order_item $order_item)
{ 
   return view('order_items.edit',compact('order_item'));
}




/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
// $order_item = Order_item::find($id);
// $order_item->ordersId = $request->ordersId;
// $order_item->quantity = $request->quantity;
// $order_item->other = $request->other;
// $order_item->unitId = $request->unitId;
// $order_item->price = $request->price;
// $order_item->dueDate = $request->dueDate;
// $order_item->openningQNT = $order_item->quantity;
// $order_item->reference = $request->reference;
// $order_item->productId = $request->productId;
// //$order_item->stateId = 44;
// $order_item->totalPrice = $request->totalPrice;
// $order_item->save();

$data['order_items'] = Order_item::where('stateId','<>', 45)
                                  ->orderBy('updated_at','desc')
                                  ->paginate(50);

  $data = [

   
    'ordersId' => $request->ordersId,
    'reference' => $request->reference,
    'order_other' => $request->other,
    'dueDate' => $request->dueDate,
    'userId' => Auth::id(),
    'quantity' => $request->quantity,
    'openningQNT' => $request->openningQNT,
    'unitId' => $request->unitId,
    'price' => $request->price,
    'productId' => $request->productId,
    'totalPrice' => $request->totalPrice,
    'id' => $id,
    'order' => $request->ordersId
   
  
  ];



 
  
  
  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 



   
  // $response = Http::get($url.'/qryorderitems/update?data='.http_build_query($data));
    
    
  // if ($response->successful()) {
 
  //  $jsonResponse = json_decode( $response, true);

  
  //  return redirect()->route('order_items.index')->with('success','Order iterm Has Been updated  successfully');


  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------

  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
                          ->retry(3, 1000) // Retry 3 times with a 1-second delay
                          ->get($url.'/qryorderitems/update?data='.http_build_query($data));
  
        //$data['info'] = json_decode($response, true);
    
        // Check if the request was successful
        if ($response->successful() ){
  
          $jsonResponse = json_decode( $response, true);
          return redirect()->route('order_items.index')->with('success','Order iterm Has Been updated  successfully');
  
        } else {
            // Throw an exception if the request fails
            return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);
  
        }
    } catch (Exception $e) {
        // Log the error
        Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
        return view('errorpage', [
          'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
      ]);
  
        // If this is the last attempt, return an error message
        // if ($attempt === $maxRetries) {
        //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
        // }
  
        if ($attempt === $maxRetries) {
          return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);
      }
  
        // Wait before retrying
        sleep($retryDelay);
    }
  }

}


/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function updatel(Request $request, $id)
{

}

/**
* Remove the specified resource from storage.
*
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function destroy(Order_item $order_item)
{




 
  if($order_item){

    $id = $order_item->id;

  }

  $data = 
  [

    'id' =>  $id ,
  ];

  $url = env('APP_URL');


  //$response = Http::get('http://localhost/LaravelCRUD/qryorders/destroy?id='.$id);

  $service_url = $url.'/qryorderitems/destroy?id='.$id;
  $curl = curl_init($service_url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $curl_response = curl_exec($curl);

  //dd($curl_response);


  
  
  if ($curl_response == '1' ) {
     

    return redirect()->route('order_items.index')->with('success','Order iterm Has Been deleted  successfully');

  
  } else {
      
      dd('Sorry , there an error with your request');
  
  }

// $data['order_items'] = Order_item::where('stateId','<>', 45)
//                                   ->orderBy('updated_at','desc')
//                                   ->paginate(50);


}


/**
* Remove the specified resource from storage.
*
* @param  \App\order_item  $order_item
* @return \Illuminate\Http\Response
*/
public function destroyl(Order_item $order_item)
{

}
public function getProductbyid(Request $request){

  $productid = $request->productid;

  $porduct = Porduct::select('*')->where('id', $productid)->get();
 
 
 // $request->session()->regenerate();

  $packagingLevelData = $request->session()->get('packagingLevel');


  $response['data'] = $porduct;

  $response['packagingLevel'] =  $packagingLevelData;

  if( $response['packagingLevel'] == null){

    $response['packagingLevel']  = Type::where('groupType', '=','PackagingLevel')->value('value');


  }



  return response()->json($response);
}


public function generate(Request $request){

 

  $productid1 = $request->productid1;
  $gusset1 = $request->gusset1;  
  $width1 = $request->width1;
  $totalWidth1 = $request->totalWidth1;
  $materialType1 = $request->materialType1;
  $colour1 = $request->colour1 ;
  $micron1 = $request->micron1;
  $bagType1 = $request->bagType1;

  $porduct = Porduct::select('*')->where('color1', $colour)
                                   ->where('materialTypeId1', $materialType)
                                    ->where('product_Width1', $width)
                                    ->where('totalWidth1', $totalWidth)
                                   ->where('gussetWidth1', $gusset)
                                    ->where('thickness1', $micron)
                                    //->where('bagType1', $bagType)
                                   ->where('productType1','=' , 101 )
                                  ->get();
  $response['data'] = $porduct;

  //Log::info("product search : ".$porduct);

  return response()->json($response);
 }


 public function generateAllocation(Request $request)
 {

  $current_time = date("H:i:s");


  $productid = $request->productid;
  $jobcarditem = $request->jobcarditem;
  $startTime = $current_time;
  $endTime = $current_time;
  $shift = $request->shift; 
  $process = $request->process; 
  $operator = $request->operator; 
  $machine = $request->machine; 
  $unit = $request->unit;
  $qnt = $request->qnt;



  
  $set = new Allocation;
  $set->jobcarditemId  = $jobcarditem;
  $set->machineId  = $machine;
  $set->userId = Auth::id();
  $set->qnt = $qnt;
  $set->operator = $operator;
  $set->endTime  = $endTime;
  $set->startTime  = $startTime;
  $set->shiftId  = $shift;
  $set->processId  = $process;
  $set->unitId  = $unit;
  $set->save();

 



  $response = $set->id;
 
  
     return response()->json($response);
 }

 
 public function updateAllocation(Request $request)
 {
  
  $productid = $request->productid;
   $jobcarditem = $request->jobcarditem;
   $startTime = $request->startTime;
   $endTime = $request->endTime;
   $shift = $request->shift; 
   $operator = $request->operator; 
   $machine = $request->machine; 
   $id = $request->allocation; 
 

  
  $set = Allocation::find($id);
  $set->machineId  = $machine;
  $set->operator = $operator;
  $set->endTime  = $endTime;
  $set->startTime  = $startTime;
  $set->shiftId  = $shift;
  $set->save();



 



  $response = $productid;
 
  
     return response()->json($response);
 }




 public function actionview(Request $request) {

  $id =   $request->order;
 
 
  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 
 

 
 
 
  // $service_url = $url.'/qryorderitems/show?id='.$id ;
  // $curl = curl_init($service_url);
  // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  // $curl_response = curl_exec($curl);
 
  
 
  // if ($curl_response == true ) {
 
  //  $orderData  = json_decode($curl_response );
 
   
   
 
  //  $service_url = $url.'/qryorders/show?itemid='.$id ;
  //  $curl = curl_init($service_url);
  //  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  //  $curl_respons = curl_exec($curl);
 
  //  $data['orders'] = json_decode($curl_response, true);
 
   
  
 
  //  $data['orderitems'] = json_decode($curl_respons, true);
   
 
   
   
 
  //  // Assuming 'orderitems' is the key containing the array of order items
  //  $orderitems = $data['orderitems'];
  //  $orders = $data['orders'];
  
  //  // Pass the $orderitems variable to the view
  //  return view('orders.show')
  //    ->with('orderitems', $orderitems)
  //    ->with('orders', $orders);
 
 
 
   
  //  //View::share('orderitems', $orderitems);
  //  // View::share('orders', $orderData  );
 
  
  //  // return view('orders.show', ['orderitems' => $orderitems]);
  
  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }


  $url = env('APP_URL');

  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // First API call: Retrieve Order Items
          $order_items_url = $url . '/qryorderitems/show?' . http_build_query(['id' => $id]);
          
          // Initialize cURL for order items
          $curl_order_items = curl_init($order_items_url);
          curl_setopt_array($curl_order_items, [
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_TIMEOUT => 10,
              CURLOPT_FAILONERROR => false,
              CURLOPT_SSL_VERIFYPEER => true,
              CURLOPT_SSL_VERIFYHOST => 2,
              CURLOPT_HTTPHEADER => [
                  'Accept: application/json',
                  'Content-Type: application/json'
              ]
          ]);

          // Execute first cURL request
          $curl_order_items_response = curl_exec($curl_order_items);

          // Check for cURL errors
          if ($curl_order_items_response === false) {
              throw new Exception('Failed to retrieve order items: ' . curl_error($curl_order_items));
          }

          // Get HTTP status for order items
          $order_items_status = curl_getinfo($curl_order_items, CURLINFO_HTTP_CODE);
          curl_close($curl_order_items);

          // Validate order items HTTP status
          if ($order_items_status !== 200) {
              throw new Exception("Order Items HTTP Error: {$order_items_status}");
          }

          // Decode order items
          $orderData = json_decode($curl_order_items_response, true);
          
          // Validate order items response
          if (empty($orderData)) {
              throw new Exception('No order items found');
          }

          // Second API call: Retrieve Orders
          $orders_url = $url . '/qryorders/show?' . http_build_query(['itemid' => $id]);
          
          // Initialize cURL for orders
          $curl_orders = curl_init($orders_url);
          curl_setopt_array($curl_orders, [
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_TIMEOUT => 10,
              CURLOPT_FAILONERROR => false,
              CURLOPT_SSL_VERIFYPEER => true,
              CURLOPT_SSL_VERIFYHOST => 2,
              CURLOPT_HTTPHEADER => [
                  'Accept: application/json',
                  'Content-Type: application/json'
              ]
          ]);

          // Execute second cURL request
          $curl_orders_response = curl_exec($curl_orders);

          // Check for cURL errors
          if ($curl_orders_response === false) {
              throw new Exception('Failed to retrieve orders: ' . curl_error($curl_orders));
          }

          // Get HTTP status for orders
          $orders_status = curl_getinfo($curl_orders, CURLINFO_HTTP_CODE);
          curl_close($curl_orders);

          // Validate orders HTTP status
          if ($orders_status !== 200) {
              throw new Exception("Orders HTTP Error: {$orders_status}");
          }

          // Decode orders
          $ordersData = json_decode($curl_orders_response, true);

          // Validate orders response
          if (empty($ordersData)) {
              throw new Exception('No orders found');
          }

          // Log successful retrieval
          Log::info('Order items and orders retrieved successfully', [
              'order_item_id' => $id,
              'order_items_count' => count($orderData),
              'orders_count' => count($ordersData)
          ]);

          // Prepare data for view
          $data = [
              'orderitems' => $orderData,
              'orders' => $ordersData
          ];

          // Return view with data
          return view('orders.show', $data);

      } catch (Exception $e) {
          // Log the error
          Log::error('Order retrieval attempt failed', [
              'order_item_id' => $id,
              'attempt' => $attempt,
              'error' => $e->getMessage()
          ]);

          // Last attempt
          if ($attempt === $maxRetries) {
              // Log final failure
              Log::error('Order retrieval failed after all attempts', [
                  'order_item_id' => $id,
                  'total_attempts' => $maxRetries
              ]);

              // Redirect with error message
              return redirect()->route('orders.index')
                  ->with('error', 'Failed to retrieve order details. Please try again later.');
          }

          // Wait before retrying
          sleep($retryDelay);
      }
  }
 
 
 }
 
 
 
 
 
 
 
 
 
 
 public function actionupdate(Request $request) {
 
   $id =  $request->order;
   
   $url = env('APP_URL');
   $maxRetries = 3; 
   $retryDelay = 2; 
 
 
 
 
  //  $service_url = $url.'/qryorderitems/show?id='.$id ;
  //  $curl = curl_init($service_url);
  //  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  //  $curl_response = curl_exec($curl);
 
 

     
 
  //  if ($curl_response == true ) {
 
     

   
  //    $data['orderitems'] = json_decode($curl_response, true);
     
   
  //    //dd($data);
     
   
  //    // Assuming 'orderitems' is the key containing the array of order items
  //    $orderitems = $data['orderitems'];
   
    
  //    // Pass the $orderitems variable to the view
  //    return view('order_items.edit')
  //      ->with('order_items', $orderitems)
  //     ;
 
  //  } else {
       
  //      dd('Sorry , there an error with your request');
   
  //  }

   for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Construct service URL with proper URL encoding
        $service_url = $url . '/qryorderitems/show?' . http_build_query(['id' => $id]);

        // Initialize cURL
        $curl = curl_init($service_url);
        
        // Set comprehensive cURL options
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,  // Return transfer as string
            CURLOPT_TIMEOUT => 10,           // Timeout after 10 seconds
            CURLOPT_FAILONERROR => false,    // Don't fail on HTTP errors to check response
            CURLOPT_SSL_VERIFYPEER => true,  // Verify SSL certificate
            CURLOPT_SSL_VERIFYHOST => 2,     // Verify host name in SSL certificate
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        ]);

        // Execute cURL request
        $curl_response = curl_exec($curl);

        // Check for cURL errors
        if ($curl_response === false) {
            throw new Exception(curl_error($curl));
        }

        // Get HTTP status code
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Close cURL resource
        curl_close($curl);

        // Validate HTTP status
        if ($http_status !== 200) {
            throw new Exception("HTTP Error: {$http_status}");
        }

        // Check for successful deletion
        if ($curl_response == true ) {
            // Log successful deletion
            Log::info('Order item deleted successfully', [
                'order_item_id' => $id,
                'url' => $service_url
            ]);

        
            $data['orderitems'] = json_decode($curl_response, true);
     
   
            //dd($data);
            
          
            // Assuming 'orderitems' is the key containing the array of order items
            $orderitems = $data['orderitems'];
          
           
            // Pass the $orderitems variable to the view
            return view('order_items.edit')
              ->with('order_items', $orderitems)
             ;
        } else {
            // Throw exception for unsuccessful deletion
            throw new Exception('Deletion failed: Unexpected response');
        }

    } catch (Exception $e) {
        // Log the error
        Log::error('Order item destroy attempt ' . $attempt . ' failed', [
            'url' => $service_url,
            'order_item_id' => $id,
            'attempt' => $attempt,
            'error' => $e->getMessage()
        ]);

        // Last attempt
        if ($attempt === $maxRetries) {
            // Log final failure
            Log::error('Order item destroy failed after ' . $maxRetries . ' attempts', [
                'order_item_id' => $id
            ]);

            // Redirect with error message
            return redirect()->route('order_items.index')
                ->with('error', 'Failed to delete order item. Please try again later.');
        }

        // Wait before retrying
        sleep($retryDelay);
    }
}
 
 }
 
 
 
 
 
 
 public function actiondel(Request $request) {
 
   $id =  $request->order;
   
   $url = env('APP_URL');
    $maxRetries = 3; 
    $retryDelay = 2; 
 
 
 
  //  $service_url = $url.'/qryorderitems/destroy?id='.$id;
  //  $curl = curl_init($service_url);
  //  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  //  $curl_response = curl_exec($curl);
 
   
  //  if ($curl_response === '1') {
      
 
     
  //    $response = json_decode($curl_response);
 
 
  //    return redirect()->route('order_items.index')
  //    ->with('success','Order successfully been deleted');
 
 
   
  //  } else {
       
  //      dd('Sorry , there an error with your request');
   
  //  }





    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Construct service URL with proper URL encoding
            $service_url = $url . '/qryorderitems/destroy?' . http_build_query(['id' => $id]);

            // Initialize cURL
            $curl = curl_init($service_url);
            
            // Set comprehensive cURL options
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,  // Return transfer as string
                CURLOPT_TIMEOUT => 10,           // Timeout after 10 seconds
                CURLOPT_FAILONERROR => false,    // Don't fail on HTTP errors to check response
                CURLOPT_SSL_VERIFYPEER => true,  // Verify SSL certificate
                CURLOPT_SSL_VERIFYHOST => 2,     // Verify host name in SSL certificate
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ]);

            // Execute cURL request
            $curl_response = curl_exec($curl);

            // Check for cURL errors
            if ($curl_response === false) {
                throw new Exception(curl_error($curl));
            }

            // Get HTTP status code
            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // Close cURL resource
            curl_close($curl);

            // Validate HTTP status
            if ($http_status !== 200) {
                throw new Exception("HTTP Error: {$http_status}");
            }

            // Check for successful deletion
            if ($curl_response === '1') {
                // Log successful deletion
                Log::info('Order item deleted successfully', [
                    'order_item_id' => $id,
                    'url' => $service_url
                ]);

                // Redirect with success message
                return redirect()->route('order_items.index')
                    ->with('success', 'Order item successfully deleted');
            } else {
                // Throw exception for unsuccessful deletion
                throw new Exception('Deletion failed: Unexpected response');
            }

        } catch (Exception $e) {
            // Log the error
            Log::error('Order item destroy attempt ' . $attempt . ' failed', [
                'url' => $service_url,
                'order_item_id' => $id,
                'attempt' => $attempt,
                'error' => $e->getMessage()
            ]);

            // Last attempt
            if ($attempt === $maxRetries) {
                // Log final failure
                Log::error('Order item destroy failed after ' . $maxRetries . ' attempts', [
                    'order_item_id' => $id
                ]);

                // Redirect with error message
                return redirect()->route('order_items.index')
                    ->with('error', 'Failed to delete order item. Please try again later.');
            }

            // Wait before retrying
            sleep($retryDelay);
        }
    }

 
 
 }
 

}
