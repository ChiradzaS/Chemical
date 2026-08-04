<?php
namespace App\Http\Controllers;
use App\Models\Orders;
use App\Models\Order_item;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;
use Exception;

class OrdersController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/

public function index(Request $request)
{

 

  $url = env('APP_URL'); // Get the base URL from the environment

  $maxRetries = 3; // Maximum number of retries
  $retryDelay = 2; // Delay between retries in seconds

  
 $hostname = gethostname();

        
  $serverIp = gethostbyname($hostname);


 //$response = Http::get($url.'/qryrestip/store?ipaddress='.$serverIp);

  

  $srchlist = $request->input('search');

  if($srchlist ){

  }



 

  //----------------------------------------------------------------------------------------------------------------------------

  $orderlist = $request->get('orders');
  $completelist = $request->get('orders');

  $id = Type::where('name', 'Complete')->value('id');

  Order_Item::where('quantity', '<=', 0)->update(['stateId' =>   $id ]);


  if($completelist == 'complete' ){

    // $data['orders'] = Orders::orderBy('updated_at','desc')->where('stateId','=', 45)->paginate(200);

    // $response = Http::get($url.'/qryorders/index?complete=complete');
  
  
    // if ($response->successful()) {
       
    //   $data['orders'] = json_decode($response);
        
    //   return view('orders.allorders',$data );
    
    // } else {
        
    //     dd('Sorry , there an error with your request');
    
    // }



for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
            ->retry(3, 1000) // Retry 3 times with a 1-second delay
            ->get($url."/qryorders/index?complete=complete");

        $data['orders'] = json_decode($response, true);
    
        // Check if the request was successful
        if ($response->successful() && !empty($data['orders'])){
          
          $data['orders'] = json_decode($response, true);
      
        
          return view('orders.allorders',$data );

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


  //-----------------------------------------------------------------------------------------------------------------------------

if($orderlist != null){

  $data['orders'] = Orders::orderBy('customerId','asc')->where('stateId','<>', 45)->paginate(200);

  return view('orders.allorders',$data);

}

//----------------------------------------------------------------------------------------------------------------------------------

$action = $request->get('action');

if ($action <> null && trim($action, ' ') == 'query'){
 

  $customerId = $request->get('customerId');



  $fromDate = $request->get('fromDate');
  if ($fromDate == null) {
    $fromDate = '2020-12-31';
  }

  $toDate = $request->get('toDate');
  if ($toDate == null) {
    $toDate = '2030-12-31';
  }


 

  $customerComp = '<>';
  if ($customerId > 0) {
     $customerComp = '=';
  }
            
                         

 $search = 10;




  //$response = Http::get("$url/qryorders/index?customerId=".$customerId.'&&toDate='.$toDate.'&&fromDate='.$fromDate.'&&customerComp='.$customerComp.'&&search='.$search);
 
 
 
  // if ($response->successful()) {
     
  //   $data['orders'] = json_decode($response, true);

    

  
  //     return view('orders.index' , $data,['customerId' => $customerId ,'toDate'=> $toDate,'fromDate' => $fromDate]);
  
  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }


for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
            ->retry(3, 1000) // Retry 3 times with a 1-second delay
            ->get($url."/qryorders/index?customerId=".$customerId.'&&toDate='.$toDate.'&&fromDate='.$fromDate.'&&customerComp='.$customerComp.'&&search='.$search);

        $data['orders'] = json_decode($response, true);
    
        // Check if the request was successful
        if ($response->successful() && !empty($data['orders'])){
          
          $data['orders'] = json_decode($response, true);
      
        
          return view('orders.index' , $data,['customerId' => $customerId ,'toDate'=> $toDate,'fromDate' => $fromDate]);

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

 //------------------------------------------------------------------------------------------------------------------------



  
  
  // $response = Http::get($url.'/qryorders/index');
  
  
  // if ($response->successful()) {
     
  //   $data['orders'] = json_decode($response, true);


  //   //Log::info($data);

  
  //     return view('orders.index' , $data,['customerId' => '' ,'toDate'=> '','fromDate' =>'']);
  
  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }




for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
            ->retry(3, 1000) // Retry 3 times with a 1-second delay
            ->get($url.'/qryorders/index'); // Use POST for storing data

        $data['orders'] = json_decode($response, true);
    
        // Check if the request was successful
        if ($response->successful() && !empty($data['orders'])){
          
          $data['orders'] = json_decode($response, true);
      
        
            return view('orders.index' , $data,['customerId' => '' ,'toDate'=> '','fromDate' =>'']);

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



 // return view('orders.index' , $data,['state' => 0 ,'productId'=> -9,'color' => -9,'bagType' => -9, 'materialTypeId' => -9 ,'fromDate' => -9,'toDate' => -9]);

 // return view('orders.index', $data,['state' => 0 ,'productId'=> -9,'color' => -9,'bagType' => -9, 'materialTypeId' => -9 ,'fromDate' => -9,'toDate' => -9]);
}







/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
  
return view('orders.create');
}



/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{




    $data = [

    'reference' => $request->reference,
    'date' => $request->date,
    'order_other' => $request->other,
    'customerId' => $request->customerId,
    'totalValue' => $request->totalValue,
    'datePlaced' => $request->datePlaced,
    'dueDate' => $request->dueDate,
    'userId' => Auth::id(),
    'quantity' => $request->quantity,
    'order_item_other' => $request->other,
    'unitId' => $request->unitId,
    'price' => $request->price,
    'orderBy' => 0,
    'openningQNT' => $request->quantity, 
    'reference' => $request->reference_item, 
    'productId' => $request->productId,
    'totalPrice' => $request->totalPrice,

];

// $url = env('APP_URL');


// //dd($url);

// $response = Http::get($url.'/qryorders/store', $data);


// if ($response->successful()) {
   
//     $orderId = $response->json(); 
  
    
//     //return redirect()->route('orders.edit', $orderId)->with('success','Orders has been created successfully.');
//     return redirect()->route('orders.index')->with('success','Orders has been created successfully.');

// } else {
    
//     dd('Sorry , there an error with your request');



// }





$url = env('APP_URL'); // Get the base URL from the environment

$maxRetries = 3; // Maximum number of retries
$retryDelay = 2; // Delay between retries in seconds

for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
            ->retry(3, 1000) // Retry 3 times with a 1-second delay
            ->get($url.'/qryorders/store', $data); // Use POST for storing data


        $orderId = $response->json(); // Get the order ID from the response
        // Check if the request was successful
        if ($response->successful() && is_numeric($orderId) ) {
          
            Log::error('Attempt  on storing new order/////////////////////// ' .$orderId );

            // Redirect with a success message
            return redirect()->route('orders.index')->with('success', 'Orders has been created successfully.');
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
* @param  \App\order  $order
* @return \Illuminate\Http\Response
*/
public function show(Request $request)
{
  //$orderitems=DB::table('order_items')->where('ordersId', $order->id)->get();

  $page =  $request->view;


  $id =  $request->order;



  
  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 




//   $service_url = $url.'/qryorders/sho?id='.$id ;
//   $curl = curl_init($service_url);
//   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//   $curl_response = curl_exec($curl);

//      //dd($curl_response );

  
  
  
//   if ($curl_response == true ) {
     
//     $orderitems = json_decode($curl_response );

   

//     foreach ($orderitems as $orderitem) {
//       $customer = $orderitem->customerId;
//       $order = $orderitem->ordersId ; 
//   }

//   //dd($customer );
  
      

//     View::share('orderitems', $orderitems );
//     // View::share('customer', $customer);
//     // View::share('order', $order);


//     //dd($orderitems);
  
//  return view('orders.edit',['order'=> $order ,'customer'=> $customer]);
  
//   } else {
      
//       dd('Sorry , there an error with your request');
  
//   }



  //--------------------------------------------------------------------------------------------------------

  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Construct service URL with proper URL encoding
        $service_url = $url . '/qryorders/sho?'.http_build_query(['id' => $id]);
        
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
          $orderitems = json_decode($curl_response );

   

          foreach ($orderitems as $orderitem) {
            $customer = $orderitem->customerId;
            $order = $orderitem->ordersId ; 
        }
      
        //dd($customer );
        
            
      
          View::share('orderitems', $orderitems );
          // View::share('customer', $customer);
          // View::share('order', $order);
      
      
          //dd($orderitems);
        
       return view('orders.edit',['order'=> $order ,'customer'=> $customer]);
        } else {
            // Throw exception for unsuccessful deletion
            throw new Exception('Deletion failed: Unexpected response');
        }
    } catch (Exception $e) {
        // Log the error
        Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
        return view('errorpage', [
          'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
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



/**
* Show the form for editing the specified resource.
*
* @param  \App\Orders  $order
* @return \Illuminate\Http\Response
*/
public function edit(Request $request)
{


  $id =  $request->order;

  

  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 


 
 
//    $service_url = $url.'/qry/update?id='.$id ;
//    $curl = curl_init($service_url);
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//    $curl_response = curl_exec($curl);

//    dd($curl_response );
 
 
      
 
   
   
   
//    if ($curl_response == true ) {
      
//      $orderitems = json_decode($curl_response );
       
 
//      View::share('orderitems', $orderitems );
   
  
// return view('orders.edit',compact('order'));
   
//    } else {
       
//        dd('Sorry , there an error with your request');
   
//    }

   //--------------------------------------------------------------------------------------------------------
   for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Construct service URL with proper URL encoding
        $service_url = $url . '/qry/update?'.http_build_query(['id' => $id]);
        
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
          $orderitems = json_decode($curl_response );
       
 
          View::share('orderitems', $orderitems );
        
       
     return view('orders.edit',compact('order'));
        } else {
            // Throw exception for unsuccessful deletion
            throw new Exception('Deletion failed: Unexpected response');
        }
    } catch (Exception $e) {
        // Log the error
        Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
        return view('errorpage', [
          'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
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




/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\order  $order
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
$request->validate([
'reference' => 'required',

]);
$order = Orders::find($id);
$order->reference = $request->reference;
$order->date = $request->date;
$order->other = $request->other;
$order->customerId = $request->customerId;
$order->totalValue = $request->totalValue;
$order->stateId = $request->stateId;
$order->save();
return redirect()->route('orders.index')
->with('success','Orders Has Been updated successfully');
}


/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\order  $order
* @return \Illuminate\Http\Response
*/
public function updatel(Request $request, $id)
  {

  }






public function actionview(Request $request) {

 $id =   $request->order;


 $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 




 $service_url = $url.'/qryorders/show?id='.$id ;
 $curl = curl_init($service_url);
 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 $curl_response = curl_exec($curl);

 

 if ($curl_response == true ) {

  $orderData  = json_decode($curl_response );

  
  

  $service_url = $url.'/qryorders/show?itemid='.$id ;
  $curl = curl_init($service_url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $curl_respons = curl_exec($curl);

  $data['orders'] = json_decode($curl_response, true);

  
 

  $data['orderitems'] = json_decode($curl_respons, true);
  

  //dd( $data['orderitems']);
  

  // Assuming 'orderitems' is the key containing the array of order items
  $orderitems = $data['orderitems'];
  $orders = $data['orders'];
 
  // Pass the $orderitems variable to the view
  return view('orders.show')
    ->with('orderitems', $orderitems)
    ->with('orders', $orders);



  
  //View::share('orderitems', $orderitems);
  // View::share('orders', $orderData  );

 
  // return view('orders.show', ['orderitems' => $orderitems]);
 
 } else {
     
             Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
        return view('errorpage', [
          'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
      ]);
 
 }


//    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
//     try {
//         // Construct service URL with proper URL encoding
//         $service_url = $url .'/qryorders/show?'.http_build_query(['itemid' => $id]);
        
//         // Initialize cURL
//         $curl = curl_init($service_url);
        
//         // Set comprehensive cURL options
//         curl_setopt_array($curl, [
//             CURLOPT_RETURNTRANSFER => true,  // Return transfer as string
//             CURLOPT_TIMEOUT => 10,           // Timeout after 10 seconds
//             CURLOPT_FAILONERROR => false,    // Don't fail on HTTP errors to check response
//             CURLOPT_SSL_VERIFYPEER => true,  // Verify SSL certificate
//             CURLOPT_SSL_VERIFYHOST => 2,     // Verify host name in SSL certificate
//             CURLOPT_HTTPHEADER => [
//                 'Accept: application/json',
//                 'Content-Type: application/json'
//             ]
//         ]);
        
//         // Execute cURL request
//         $curl_response = curl_exec($curl);
        
//         // Check for cURL errors
//         if ($curl_response === false) {
//             throw new Exception(curl_error($curl));
//         }
        
//         // Get HTTP status code
//         $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
//         // Close cURL resource
//         curl_close($curl);
        
//         // Validate HTTP status
//         if ($http_status !== 200) {
//             throw new Exception("HTTP Error: {$http_status}");
//         }
        
//         // Check for successful deletion
//         if ($curl_response == true ) {
//           $orderData  = json_decode($curl_response );
  
    
    
  
//           // $service_url = $url.'/qryorders/update?itemid='.$id ;
//           // $curl = curl_init($service_url);
//           // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//           // $curl_respons = curl_exec($curl);
        
//           $data['orders'] = json_decode($curl_response, true);

//           $data['orderitems'] = json_decode($curl_respons, true);
          
        
          
          
        
//           // Assuming 'orderitems' is the key containing the array of order items
//           $orderitems = $data['orderitems'];
//           $orders = $data['orders'];
         
//           // Pass the $orderitems variable to the view
//           return view('orders.edit')
//             ->with('orderitems', $orderitems)
//             ->with('orders', $orders);
//         } else {
//             // Throw exception for unsuccessful deletion
//             throw new Exception('Deletion failed: Unexpected response');
//         }
//     } catch (Exception $e) {
//         // Log the error
//         Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
//         return view('errorpage', [
//           'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
//       ]);
        
//         // Last attempt
//         if ($attempt === $maxRetries) {
//             // Log final failure
//             Log::error('Order item destroy failed after ' . $maxRetries . ' attempts', [
//                 'order_item_id' => $id
//             ]);
            
//             // Redirect with error message
//             return redirect()->route('order_items.index')
//                 ->with('error', 'Failed to delete order item. Please try again later.');
//         }
        
//         // Wait before retrying
//         sleep($retryDelay);
//     }
//   }


}










public function actionupdate(Request $request) {

  $id =  $request->order;
  
  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 




  $service_url = $url.'/qryorders/update?id='.$id ;
  $curl = curl_init($service_url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $curl_response = curl_exec($curl);


 
    

  if ($curl_response == true ) {

    $orderData  = json_decode($curl_response );
  
    
    
  
    $service_url = $url.'/qryorders/update?itemid='.$id ;
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_respons = curl_exec($curl);
  
    $data['orders'] = json_decode($curl_response, true);
  
    
   
  
    $data['orderitems'] = json_decode($curl_respons, true);
    
  
    
    
  
    // Assuming 'orderitems' is the key containing the array of order items
    $orderitems = $data['orderitems'];
    $orders = $data['orders'];
   
    // Pass the $orderitems variable to the view
    return view('orders.edit')
      ->with('orderitems', $orderitems)
      ->with('orders', $orders);

  } else {
      
           Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
  return view('errorpage', [
    'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
]);
  
  }


  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Construct service URL with proper URL encoding
        $service_url = $url . '/qryorders/update?'.http_build_query(['itemid' => $id]);
        
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
          $orderData  = json_decode($curl_response );
  
    
    
  
          // $service_url = $url.'/qryorders/update?itemid='.$id ;
          // $curl = curl_init($service_url);
          // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          // $curl_respons = curl_exec($curl);
        
          $data['orders'] = json_decode($curl_response, true);

          $data['orderitems'] = json_decode($curl_respons, true);
          
        
          
          
        
          // Assuming 'orderitems' is the key containing the array of order items
          $orderitems = $data['orderitems'];
          $orders = $data['orders'];
         
          // Pass the $orderitems variable to the view
          return view('orders.edit')
            ->with('orderitems', $orderitems)
            ->with('orders', $orders);
        } else {
            // Throw exception for unsuccessful deletion
            throw new Exception('Deletion failed: Unexpected response');
        }
    } catch (Exception $e) {
        // Log the error
        Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
        return view('errorpage', [
          'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
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




  // $service_url = $url.'/qryorders/destroy?id='.$id;
  // $curl = curl_init($service_url);
  // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  // $curl_response = curl_exec($curl);

  
  // if ($curl_response === '1') {
     

    
  //   $response = json_decode($curl_response);


  //   return redirect()->route('orders.index')
  //   ->with('success','Order successfully been deleted');


  
  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }


  
  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Construct service URL with proper URL encoding
        $service_url = $url . '/qryorders/destroy?' . http_build_query(['id' => $id]);
        
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
        if ($curl_response == '1') {
            // Log successful deletion
            Log::info('Order item deleted successfully', [
                'order_item_id' => $id,
                'url' => $service_url
            ]);
            
           
    
            $response = json_decode($curl_response);


            return redirect()->route('orders.index')
            ->with('success','Order successfully been deleted');
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



public function actiondelete(Request $request) {




}


public function actionupdate1(Request $request) {
 
  $id =  $request->order;

  //dd($id );
  
  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 




  // $service_url = $url.'/qryorderitems/show?id='.$id ;
  // $curl = curl_init($service_url);
  // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  // $curl_response = curl_exec($curl);



    

  // if ($curl_response == true ) {

    

  
  //   $data['orderitems'] = json_decode($curl_response, true);
    
  
  //  // dd($data);
    
  
  //   // Assuming 'orderitems' is the key containing the array of order items
  //   $orderitems = $data['orderitems'];
  
   
  //   // Pass the $orderitems variable to the view
  //   return view('order_items.edit')
  //     ->with('order_items', $orderitems)
  //    ;

  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }

  //------------------------------------------------------------------------------------------------------------------------------------------------

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
        if ($curl_response == true) {
            // Log successful deletion
            Log::info('Order item deleted successfully', [
                'order_item_id' => $id,
                'url' => $service_url
            ]);
            
           
    $data['orderitems'] = json_decode($curl_response, true);
    
  
    // dd($data);
     
   
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






public function actiondel2(Request $request) {

  $id =  $request->order;
  
  
  $url = env('APP_URL');
  $maxRetries = 3; 
  $retryDelay = 2; 




  // $service_url = $url.'/qryorderitems/destroy?id='.$id;
  // $curl = curl_init($service_url);
  // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  // $curl_response = curl_exec($curl);

  
  // if ($curl_response === '1') {
     

    
  //   $response = json_decode($curl_response);


  //   return redirect()->route('order_items.index')
  //   ->with('success','Order successfully been deleted');


  
  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }



  //''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

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
