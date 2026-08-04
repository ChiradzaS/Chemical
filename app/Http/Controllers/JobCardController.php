<?php
namespace App\Http\Controllers;


require (base_path().'\App\Library\JobCardsRpt.php');

include_once base_path().'\App\Library\JobCardsRpt.php';

use App\Models\JobCard;
use App\Models\Porduct;
use App\Models\Order_item;
use App\Models\Jobcarditem;
use App\Models\DocumentAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Barcode\Barcode;
use App\Library\UniqueCode;
use DB;
use JobCardsRpt;
use Auth;
use Exception;
use Illuminate\Support\Facades\File;

class JobCardController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(Request $request)
{


  $url = env('APP_URL1');
  
  $maxRetries = 3; // Maximum number of retries
  $retryDelay = 2; // Delay between retries in seconds



  $completelist = $request->get('jocardtype');


  if ($completelist) {

    //dd('bring complete list');

    $data['job_cards'] = JobCard::whereNull('jobcardType')
                                ->where('stateId','=', 45 )
                                ->orderBy('id','desc')
                                ->paginate(30);

    return view('jobcarditems.jobcardstocklist', $data);

  }





    
  $action = $request->get('action');

  $allocation = $request->get('allocation');




  if ($completelist) {

    //dd('bring complete list');

    $data['job_cards'] = JobCard::whereNull('jobcardType')
                                ->where('stateId','=', 45 )
                                ->orderBy('id','desc')
                                ->paginate(30);

    return view('jobcarditems.jobcardstocklist', $data);

  }


   if ($allocation <> null && trim($allocation, ' ') == 'allocation') {





  
  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
                         ->retry(3, 1000) // Retry 3 times with a 1-second delay
                         ->get($url.'/qryjobcards/index?allocation=".$allocation');

            $data['job_cards'] = json_decode($response);
    
        // Check if the request was successful
        if ($response->successful() && !empty($data['job_cards'])){
          
          $data['job_cards'] = json_decode($response);

          return  view('job_cards.index ', $data, ['fromDate' => ' ', 'toDate' => ' ', 'startDate' => ' ', 'customerId' => -9, 'productId' => -9, ]);



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


  if ($action <> null && trim($action, ' ') == 'query') {


    $productId = $request->get('productId');
    $customerId = $request->get('customerId');
    $completelist = $request->get('jocardtype');
    $startDate = $request->get('startDate');
    

    $customerComp = '<>';
    if ($customerId > 0) {
       $customerComp = '=';
    }

    $productComp = '<>';
    if ($productId > 0) {
       $productComp = '=';
    }

    $search = 10;

    $fromDate = $request->get('fromDate');
    if ($fromDate == null) {
      $fromDate = '2020-12-31';
    }
  
    $toDate = $request->get('toDate');
    if ($toDate == null) {
      $toDate = '2030-12-31';
    }

  



  


    // $response = Http::get("$url/qryjobcards/index?customerId=".$customerId.'&&toDate='.$toDate.'&&fromDate='.$fromDate.'&&customerComp='.$customerComp.'&&productComp='.$productComp.'&&productId='.$productId.'&&search='.$search);
 
 
 
    // if ($response->successful()) {
       
    //   $data['job_cards'] = json_decode($response);


    //   //dd($data);

    //   return  view('job_cards.index',$data, ['fromDate' => $fromDate, 'toDate' => $toDate,  'customerId' => $customerId, 'productId' => $productId ]);

      
    
    // } else {
        
    //     dd('Sorry , there an error with your request');
    
    // }


    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Make the HTTP request
          $response = Http::timeout(10) // Set a timeout of 10 seconds
              ->retry(3, 1000) // Retry 3 times with a 1-second delay
              ->get("$url/qryjobcards/index?customerId=".$customerId.'&&toDate='.$toDate.'&&fromDate='.$fromDate.'&&customerComp='.$customerComp.'&&productComp='.$productComp.'&&productId='.$productId.'&&search='.$search);
  
              $data['job_cards'] = json_decode($response);
      
          // Check if the request was successful
          if ($response->successful() && !empty($data['job_cards'])){
            
            $data['job_cards'] = json_decode($response);
      
            return  view('job_cards.index',$data, ['fromDate' => $fromDate, 'toDate' => $toDate,  'customerId' => $customerId, 'productId' => $productId ]);


  
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
 

  // $response = Http::get($url.'/qryjobcards/index');
  
  
  // if ($response->successful()) {
     
  //   $data['job_cards'] = json_decode($response);

   

  
  //   return  view('job_cards.index ', $data, ['fromDate' => ' ', 'toDate' => ' ', 'startDate' => ' ', 'customerId' => -9, 'productId' => -9, ]);
  
  // } else {
    
      
  //     dd('Sorry , there an error with your request');
  
  // }


  
  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
                         ->retry(3, 1000) // Retry 3 times with a 1-second delay
                         ->get($url.'/qryjobcards/index');

            $data['job_cards'] = json_decode($response);
    
        // Check if the request was successful
        if ($response->successful() && !empty($data['job_cards'])){
          
          $data['job_cards'] = json_decode($response);

          return  view('job_cards.index ', $data, ['fromDate' => ' ', 'toDate' => ' ', 'startDate' => ' ', 'customerId' => -9, 'productId' => -9, ]);



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


 

  //return  view('job_cards.index ', $data, ['fromDate' => ' ', 'toDate' => ' ', 'startDate' => ' ', 'customerId' => -9, 'productId' => -9, 'stateId' => -9]);
}
/**
* Show the form for creating a new resource.
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function create(Request $request)
{

  return view('job_cards.create');
}

/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{  

  $orderNo = $request->get('orderId');
  $orderitem = $request->get('orderitem');

  $url = env('APP_URL1');
    

 

  


 
      $request->validate([
        'startDate' => 'required',
        'productId' => 'required',
        'customerId'=> 'required',
        'barcode'=> 'required',
        ]);


        $image = $request->file('image');
        
        if ($image) {
          
          $filename = Str::random(10) . '.' . $image->getClientOriginalExtension();
          $path = $image->storeAs('images', $filename, 'public');

      } else {
          
          $filename = null;
          $path = null;
      }

     

      $order = $request->orderId;
      
    
    
      $data = [

        'refNo' => $request->refNo,
        'description' => $request->description,
        'startDate' => $request->startDate,
        'barcode' => $request->barcode,
        'productId' => $request->productId,
        'unitId' => $request->unitId,
        'noOfProcesses' => $request->noOfProcesses,
        'qnt' => $request->qnt,
        'weightper1000 ' => $request->weightper1000,
        'bagType' => $request->bagType,
        'customerId' => $request->customerId,
        'orderId ' => 99,
        'other' => $request->other,
        'stateId' => 61, 
        'image_path' => $path,
        'order' => $order,
        'userId' => Auth::id(),     
    ];
    
    $noProcesses = $request->processQnt;



  


  
    
    $response = Http::get($url.'/qryjobcards/store',$data);
    
    
    if ($response->successful()) {

      $jsonResponse = json_decode( $response , true);

      $id = $jsonResponse['id'] ?? null;
      $bagType = $jsonResponse['bagType'] ?? null;
      $orderId = $jsonResponse['orderId'] ?? null;
  
     

      for ($i = 1; $i <= $noProcesses; $i++) {
      
        $jobcarditem = new Jobcarditem;
        $jobcarditem->jobCardId = $id;
        $jobcarditem->bagType =  $bagType;
        $process = 'processid_'.$i;
        $valProcessId = ""; 
        $valProcessId = $request->input($process);
        if ($valProcessId != "") {
        $jobcarditem->processId = $valProcessId;
        $productId = 'productId_'.$i;
        $valProdId = $request->input($productId);
        $qnt = 'qnt_'.$i;
        $valQnt = $request->input($qnt);
        $jobcarditem->qnt = $valQnt; 
        $jobcarditem->outstanding = $valQnt;
        $unitId = 'unitId_'.$i;
        $valUnitId = $request->input($unitId);
        $jobcarditem->unitId = $valUnitId;  
        $jobcarditem->barcode = UniqueCode::uniqidRealVal();
        $jobcarditem->other= $job_card->other??'';
        $jobcarditem->userId = Auth::id();
        $jobcarditem->name = $jobcarditem->processId.$jobcarditem->barcode;
       
 
 
 
        $data = [

          'jobCardId' =>  $jobcarditem->jobCardId,
          'bagType' =>$jobcarditem->bagType,
          'processId' =>$jobcarditem->processId ,
          'userId' =>$jobcarditem->userId, 
          'other' =>$jobcarditem->other,
          'barcode' =>$jobcarditem->barcode ,
          'unitId' =>$jobcarditem->unitId ,
          'outstanding' =>$jobcarditem->outstanding ,
          'name' => $jobcarditem->name,
          'qnt' =>     $jobcarditem->qnt,
          'productId' =>      $valProdId,
          'orderId' =>   $orderId,
          'item' => $noProcesses
  
         ];

         //dd($data);

        
 
         $respons = Http::get($url.'/qryjobcards/store',$data);

        
      }
    }

    
    return redirect()->route('job_cards.index')
    ->with('success','Jobcard has been created successfully.');

             
    
    
    }



    
// for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
//   try {
//       // Make the HTTP request
//       $response = Http::timeout(10) // Set a timeout of 10 seconds
//                       ->retry(3, 1000) // Retry 3 times with a 1-second delay
//                       ->get($url.'/qryjobcards/store',$data);

//       $data['orders'] = json_decode($response, true);
  
//       // Check if the request was successful
//       if ($response->successful()){
        
//         $data['orders'] = json_decode($response, true);
    
      
//         return view('orders.allorders',$data );

//       } else {
//           // Throw an exception if the request fails
//           return view('errorpage', [
//             'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
//         ]);

//       }
//   } catch (Exception $e) {
//       // Log the error
//       Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
//       return view('errorpage', [
//         'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
//     ]);

//       // If this is the last attempt, return an error message
//       // if ($attempt === $maxRetries) {
//       //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
//       // }

//       if ($attempt === $maxRetries) {
//         return view('errorpage', [
//             'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
//         ]);
//     }

//       // Wait before retrying
//       sleep($retryDelay);
//   }
// }



}
/**
* Display the specified resource.
*
* @param  \App\job_card  $job_card
* @return \Illuminate\Http\Response
*/ 
public function show(JobCard $job_card,Request $request)
{
  $myButton= $request->get('$job_card->id');
  //  echo "<pre>";
  //   print_r($myButton);
  //    exit;
  //$porduct = Porduct::find( $job_card->productId );
  $product =DB::table('porducts')->where('id', $job_card->productId )->first();
      View::share('product', $product);
      //echo "<pre>";
    //print_r($porduct);
     // exit;
   $jobcarditems=DB::table('jobcarditems')->where('jobCardId', $job_card->id)->get();
  
   View::share('jobcard', $job_card);
   View::share('jobcarditems',$jobcarditems);

  return view('job_cards.show',compact('job_card'));
} 
/**
* Show the form for editing the specified resource.
*
* @param  \App\job_card  $job_card
* @return \Illuminate\Http\Response
*/
public function edit(JobCard $job_card,Request $request)
{

  $product =DB::table('porducts')->where('id', $job_card->productId )->get();
  View::share('products', $product);
    
  $jobcarditems=DB::table('jobcarditems')->where('jobCardId', $job_card->id)->get();
  
   View::share('jobcard', $job_card);
   View::share('jobcarditems',$jobcarditems);
   

   

   foreach ($jobcarditems as $jobcarditem){
      $jobCardItemId = $jobcarditem->id;
      $productId = $jobcarditem->productId;

      

    

      

   } 


   return view('job_cards.edit',compact('job_card'));
}
/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\job_card  $job_card
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{

  $myButton= $request->get('myButton');

  if ($myButton == "clone") {
    $job_card = JobCard::find($id);
    $id = $request->id;
    $refNo = $request->refNo;
    $description = $request->description;
    $start = $request->startDate;
    $product= $request->product;
    $productId = $request->productId;
    $unit = $request->unitId;
    $process = $request->noOfProcesses;
    $qntUnitId = $request->qntUnitId;
    $qnt = $request->qnt;
    $bagType = $request->bagType;
    $qntType = $request->qntType;
    $qntUnit = $request->qntUnit;
    $orderId = $request->orderId;
    $customer = $request->customerId;
    $stateId = 61;


    


    $job_card = new JobCard;
    $job_card->refNo = Barcode::uniqidReal();
    $job_card->description = $description;
    $job_card->startDate = now();
    $job_card->barcode = Barcode::uniqidReal();
    $job_card->productId = $productId;
    $job_card->unitId = $unit;
    $job_card->noOfProcesses = $process;
    $job_card->qnt = $qnt;
    $job_card->bagType = $bagType;
    $job_card->orderId = $orderId;
    $job_card->customerId =  $customer;
    $job_card->other = "none";
    $job_card->stateId = 61;
    $job_card->userId = Auth::id();
    $job_card->save();


    $jobcarditemList = DB::table('jobcarditems')->where('jobCardId',$id)->get();
    
    

    foreach( $jobcarditemList as $item)
    {   
      $jobcarditem = new Jobcarditem;
      $jobcarditem->jobCardId =  $job_card->id;
      $jobcarditem->bagType = $item->bagType;
      $jobcarditem->processId = $item->processId;
      $jobcarditem->productId = $item->productId; 
      $jobcarditem->qnt = $item->qnt; 
      $jobcarditem->unitId = $item->unitId; 
      $jobcarditem->barcode = Barcode::uniqidReal();
      $jobcarditem->other= $item->other;
      $jobcarditem->stateId= 61;
      $jobcarditem->userId = Auth::id();
      $jobcarditem->name = $item->name;
      $jobcarditem->save();


      return redirect()->route('job_cards.index')
      ->with('success','You have  successfully added a new Cloned jobcard');

    }

  

  }


$request->validate([
  'name' => 'required',
  ]);
  $job_card = JobCard::find($id);
  $job_card->name = $request->name;
  $job_card->refNo = $request->refNo;
  $job_card->description = $request->description;
  $job_card->startDate = $request->startDate;
  $job_card->product = $request->product;
  $job_card->productId = $request->productId;
  $job_card->unit = $request->unit;
  $job_card->noOfProcesses = $request->noOfProcesses;
  $job_card->qntUnitId= $request->qntUnitId;
  $job_card->qnt = $request->qnt;
  $job_card->bagType = $request->bagType;
  $job_card->weightper1000 = $weightper1000;
  $job_card->qntType = $request->qntType;
  $job_card->qntUnit = $request->qntUnit;
  $job_card->customer = $request->customer;
  $job_card->stateId =61;
  $job_card->userId = Auth::id();
  $job_card->save();
  
  return redirect()->route('job_cards.index')
  ->with('success','Product Has Been updated successfully');
}


/**
* Remove the specified resource from storage.
*
* @param  \App\JobCard  $job_card
* @return \Illuminate\Http\Response
*/
public function destroy(JobCard $job_card)
{
$job_card->delete();
return redirect()->route('job_cards.index')
->with('success','Jobcard has been deleted successfully');
}




 
public function actionview(Request $request) {
 
  $id =  $request->job;

  
  $url = env('APP_URL1');
  $maxRetries = 3; // Maximum number of retries
  $retryDelay = 2;


  
  // $response = Http::get($url.'/qryjobcards/show?id='.$id);
   
   
  // if ($response->successful()) {
 
  //  $jsonResponse = json_decode( $response, true);
  

  //  $product = $jsonResponse['product'] ?? null;
  //  $jobcarditem = $jsonResponse['jobcarditems'] ?? null;
  //  $jobcard = $jsonResponse['jobcard'] ?? null;
  //  //dd($product);

   
  
  //   return view('job_cards.edit',['product' => $product,  'jobcarditems' =>  $jobcarditem   ,  'job_card' =>  $jobcard ]);

  // } else {
      
  //     dd('Sorry , there an error with your request');
  
  // }

      
for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
  try {
      // Make the HTTP request
      $response = Http::timeout(10) // Set a timeout of 10 seconds
                      ->retry(3, 1000) // Retry 3 times with a 1-second delay
                      ->get($url.'/qryjobcards/show?id='.$id);
  
      
      if ($response->successful()){
        
 
        $jsonResponse = json_decode( $response, true);
  

        $product = $jsonResponse['product'] ?? null;
        $jobcarditem = $jsonResponse['jobcarditems'] ?? null;
        $jobcard = $jsonResponse['jobcard'] ?? null;
    
     
        
       
         return view('job_cards.edit',['product' => $product,  'jobcarditems' =>  $jobcarditem   ,  'job_card' =>  $jobcard ]);

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
 
 
 
 
 
 
 
 
 
 
 public function actionupdate(Request $request) {
 
   $id =  $request->job;

   
   $url = env('APP_URL1');
   $maxRetries = 3; // Maximum number of retries
   $retryDelay = 2; 
 

   
 
 
   
  //  $response = Http::get($url.'/qryjobcards/show?id='.$id);
    
    
  //  if ($response->successful()) {
  
  //   $jsonResponse = json_decode( $response, true);
   

  //   $product = $jsonResponse['product'] ?? null;
  //   $jobcarditem = $jsonResponse['jobcarditems'] ?? null;
  //   $jobcard = $jsonResponse['jobcard'] ?? null;
  //   //dd($product);

    
   
  //    return view('job_cards.edit',['product' => $product,  'jobcarditems' =>  $jobcarditem   ,  'job_card' =>  $jobcard ]);
 
  //  } else {
       
  //      dd('Sorry , there an error with your request');
   
  //  }


   for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
                        ->retry(3, 1000) // Retry 3 times with a 1-second delay
                        ->get($url.'/qryjobcards/show?id='.$id);
    
        
        if ($response->successful()){
          
          $jsonResponse = json_decode( $response, true);
   

          $product = $jsonResponse['product'] ?? null;
          $jobcarditem = $jsonResponse['jobcarditems'] ?? null;
          $jobcard = $jsonResponse['jobcard'] ?? null;
          //dd($product);
      
          
         
           return view('job_cards.edit',['product' => $product,  'jobcarditems' =>  $jobcarditem   ,  'job_card' =>  $jobcard ]);
  
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
 
 
 
 
 
 
 public function actiondelete(Request $request) {
 
   $id =  $request->job;
   
   $url = env('APP_URL1');
   $maxRetries = 3; // Maximum number of retries
   $retryDelay = 2;
 

 
 
  //  $service_url = $url.'/qryjobcards/destroy?id='.$id;
  //  $curl = curl_init($service_url);
  //  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  //  $curl_response = curl_exec($curl);
 
   
  //  if ($curl_response === '1') {
      
 

 
 
  //    return redirect()->route('job_cards.index')
  //    ->with('success','Order successfully been deleted');
 
 
   
  //  } else {
       
  //      dd('Sorry , there an error with your request');
   
  //  }


  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Construct service URL with proper URL encoding
        $service_url = $url . '/qryjobcards/destroy?' . http_build_query(['id' => $id]);

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

           if ($curl_response === '1' ) {

          return redirect()->route('job_cards.index')
          ->with('success','Order successfully been deleted');

   }

 

 
 

    } catch (Exception $e) {
        // Log the error
        Log::error('Production items retrieval attempt ' . $attempt . ' failed', [
            'url' => $service_url,
            'job_card_id' => $id,
            'attempt' => $attempt,
            'error' => $e->getMessage()
        ]);

        // Last attempt
        if ($attempt === $maxRetries) {
            // Log final failure
            Log::error('Production items retrieval failed after ' . $maxRetries . ' attempts', [
                'job_card_id' => $id
            ]);

            // Redirect with error message
            return redirect()->route('job_cards.index')
                ->with('error', 'Failed to retrieve production items. Please try again later.');
        }

        // Wait before retrying
        sleep($retryDelay);
    }
}


}

public function clonejobcard(Request $request){

  $jobId =  $request->jobCardId ;



$jobcarditems = DB::table('jobcarditems')
->where('jobCardId', $jobId)
->where('processId', 24)
->get();


$hasProductId = $jobcarditems->pluck('productId')->filter()->isNotEmpty();

if ($hasProductId) {

$productIds = $jobcarditems->pluck('productId')->filter();
} else {

$jobcarditems = DB::table('jobcarditems')
    ->where('jobCardId', $jobId)
    ->get();


$productIds = $jobcarditems->pluck('productId')->filter();
}



$response=$productIds;

  
  return response()->json($response);




}





public function actionproduction(Request $request) {
 
  $id =  $request->job;

  
  $url = env('APP_URL1');
  $maxRetries = 3; // Maximum number of retries
  $retryDelay = 2;

 




  $response = Http::get($url.'/qryjobcards/productionj?id='.$id);



//   if ( $response->successful()) {

    
    
//           $data['productions'] = json_decode($response->body(), true);

//          return view('productionitems.index', $data);



 
 
 
//  } else {
     
//      dd('Sorry , there an error with your request');
 
//  }


 
 for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
  try {
      // Make the HTTP request
      $response = Http::timeout(10) // Set a timeout of 10 seconds
                      ->retry(3, 1000) // Retry 3 times with a 1-second delay
                      ->get($url.'/qryjobcards/productionj?id='.$id);
  
      
      if ($response->successful()){
        
        $data['productions'] = json_decode($response->body(), true);

        return view('productionitems.index', $data);






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




}
