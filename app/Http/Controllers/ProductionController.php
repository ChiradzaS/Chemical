<?php
namespace App\Http\Controllers;

include_once base_path().'\App\Library\ProductionRptList.php';

use App\Models\Production;
use App\Models\Jobcarditem;
use App\Models\Stock;
use App\Models\StocksTrans;
use App\Models\Jobcard;
use App\Models\Workspace;
use App\Models\Productionitem;
use App\Models\DocumentAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use App\Library\SerialNo;
use App\Barcode\Barcode;
use DB;
use Auth;
use Carbon\Carbon;
use DateTime;

use ProductionRptList;

class ProductionController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(Request $request)
{

   $itemIdToDelete = $request->query('delete');
   //$itemIdToproductionDelete = $request->query('deleteinproduction');

   if ($itemIdToDelete) {

      Productionitem::where('id', $itemIdToDelete)
                    ->update(['stateId' => 134 ]);
       // Your delete logic goes here
   }



  

   $url = env('APP_URL');
   $maxRetries = 3; 
   $retryDelay = 2; 

   $prvshift = $request->get('prvshift');
   $fromDate = $request->get('fromDate');
   $toDate = $request->get('toDate');
   $action = $request->get('action');
   $machineryId = $request->get('machineryId');
   $jobcardId = $request->get('jobcardId');
   $shiftId = $request->get('shiftId');
   $processId = $request->get('processId');
   $employeeId = $request->get('employeeId');
   //$user = $request->get('user');




   
                                      

   if ($action <> null && trim($action, ' ') == 'query') {


      $fromDate = $request->get('fromDate');

      if (is_null($fromDate)) {
         // If $fromDate is null, set it to today's date
         $fromDate = Carbon::today()->toDateString(); // Sets to today's date
     }

      $toDate = $request->get('toDate');
      if ($toDate == null) {
      $toDate = '2030-12-31';
      }
      


      $processComp = '<>';
      if ($processId > 0) {
         $processComp = '=';
      }

      $employeeComp = '<>';
      if ($employeeId > 0) {
         $employeeComp = '=';
      }

      $shiftComp = '<>';
      if ($shiftId > 0) {
         $shiftComp = '=';
      }

      $machineryComp = '<>';
      if ($machineryId > 0) {
         $machineryComp = '=';
      }

      $jobComp = '<>';
      if ($jobcardId > 0) {
         $jobComp = '=';
      }


      $data = [

         'fromDate' => $fromDate,
         'toDate'  => $toDate,
         'toDate'  => $toDate,
         'jobcardId' => $jobcardId,
         'jobComp' => $jobComp,
         'machineryId' => $machineryId,
         'shiftId'  => $shiftId ,
         'processId' =>  $processId ,
         'employeeId'  =>   $employeeId ,
         'shiftComp' =>  $shiftComp ,
         'processComp'  =>  $processComp,
         'machineryComp'   =>   $machineryComp ,
         'search'   =>   10, 
         'date' => $fromDate,

      ];

      //dd($toDate);



      

      // $response = Http::get($url.'/qryproduction/index?data='.http_build_query($data));
 
 
 
      // if ($response->successful()) {

 
      //    $data['productions'] = json_decode($response->body(), true);

      //    return view('productions.index', $data , [ 'machineryId' => -9, 'shiftId' => -9,'processId' => -9 ,'toDate' => $toDate , 'fromDate'=> $fromDate]); 
      
      // } else {
          
      //     dd('Sorry , there an error with your request');
      
      // }    
      
      
      for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
         try {
             // Make the HTTP request
             $response = Http::timeout(10) // Set a timeout of 10 seconds
                               ->retry(3, 1000) // Retry 3 times with a 1-second delay
                               ->get($url.'/qryproduction/index?data='.http_build_query($data));
       
             //$data['info'] = json_decode($response, true);
         
             // Check if the request was successful
             if ($response->successful() ){
       
               $data['productions'] = json_decode($response->body(), true);

               return view('productions.index', $data , [ 'machineryId' => -9, 'shiftId' => -9,'processId' => -9 ,'toDate' => $toDate , 'fromDate'=> $fromDate]); 
            
       
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
   

//    $response = Http::get($url.'/qryproduction/index');
  
  
//    if ($response->successful()) {


      
//       $data['productions'] = json_decode($response->body(), true);

//       $toDate = '';
//       $fromDte = '';

//       return view('productions.index', $data, [ 'machineryId' => -9, 'shiftId' => -9,'processId' => -9,'toDate' => $toDate , 'fromDate'=> $fromDate]);
      
//   } else {
       
//        dd('Sorry , there an error with your request');
   
//    }

   for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Make the HTTP request
          $response = Http::timeout(10) // Set a timeout of 10 seconds
                            ->retry(3, 1000) // Retry 3 times with a 1-second delay
                            ->get($url.'/qryproduction/index');
    
          //$data['info'] = json_decode($response, true);
      
          // Check if the request was successful
          if ($response->successful() ){
    
            $data['productions'] = json_decode($response->body(), true);

            $toDate = '';
            $fromDte = '';

            return view('productions.index', $data, [ 'machineryId' => -9, 'shiftId' => -9,'processId' => -9,'toDate' => $toDate , 'fromDate'=> $fromDate]);
            
    
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
   $productionId = $request->get('stock');

   if( $productionId != null){

       return view('productions.stock',['productionId' => $productionId ]);

   }


   
     $button = $request->get('btrn');
      $productionId = $request->get('ProductionId'); 
      $docType = ('productionItem');
   
      $productionList=DB::table('productionitems')->where('productionId',$productionId)->pluck('id');


                                    //  echo "<pre>";
                                    //  print_r(''.$productionList);
                                    //  exit;

      if ($button == 'audit')
      {

        $data['audits'] = DocumentAudit::wherein('docId',$productionList)
                                          //->where('docType', $docType)
                                          //->where('docType', $docTypes)
                                          //->where('docId',$jobCardId)
                                          ->paginate(50);  

                                                 
         return view('audits.index', $data);  
      }

  $fromDate = $request->get('fromDate');
 

  $datas = $request->all();
  foreach ($datas as $data) {
     Log::info(' Data Key : '.$data->key.' Value : '.$data->value);
  }

//   $document = new DocumentAudit();
//   $document->docId =  $jobcarditemId;
//   $document->docType = 'Jobcarditem R&P'; 
//   $document->stateId = $stateId ;
//   $document->other = $other;
//   $document->userId = 1;
//   $document->action = 'Started';
//   $document->save(); 

return view('productions.create');
}



/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
 


   $fromDate = $request->get('fromDate');
  

$request->validate([
   'processId' => 'required',
   'machineryId' => 'required',
   'employeeId' => 'required',
   'shiftId' => 'required'
   ]);         
   
   
$production = new Production;
$production->refNo = $request->refNo;
$production->other = $request->other;
$production->value = $request->value;
$production->processId = $request->processId;
$production->machineryId = $request->machineryId;
$production->employeeId = $request->employeeId;
$production->serialNo = SerialNo::generateSerialNumber();
$production->userId = $request->userId;
$production->stateId = 62;
$production->shiftId = $request->shiftId;
$production->userId = Auth::id();
$production->prodDate = $request->prodDate;
$production->startTime = $date('H:i:s');
$production->currentJobcard = $request->currentJobcard;
$production->save();

    $document = new DocumentAudit();
    $document->docId = $production->id ;
    $document->docType = 'Production started'; 
    $document->stateId  = 61;
    $document->other = 0;
    $document->userId = Auth::id();
    $document->action = 'Started';
    $document->save();

  return redirect()->route('productions.index')->with('success','Production has been created successfully.');
}





/**
* Display the specified resource.
*
* @param  \App\production  $production
* @return \Illuminate\Http\Response
*/
public function show(Production $production,Request $request)
{
 


   $productionitems=DB::table('productionitems')->where('productionId', $production->id)->get();


   $data =  $production->id;

   $url = env('APP_URL');
   $maxRetries = 3; 
   $retryDelay = 2; 

   $response = Http::get($url.'/qryproduction/show?id='.$data );
 
 
 
//    if ($response->successful()) {


//      $jsonResponse = json_decode( $response, true);
  

//      $production = $jsonResponse['production'] ?? null;
//      $productionitems = $jsonResponse['productionitems'] ?? null;

//      //dd($productionitems);

// return view('productions.show',['production' => $production ,  'productionitems' =>  $productionitems  ]);
  

   
//    } else {
       
//        dd('Sorry , there an error with your request');
   
//    }  

   for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Make the HTTP request
          $response = Http::timeout(10) // Set a timeout of 10 seconds
                            ->retry(3, 1000) // Retry 3 times with a 1-second delay
                            ->get($url.'/qryproduction/show?id='.$data );
    
          //$data['info'] = json_decode($response, true);
      
          // Check if the request was successful
          if ($response->successful() ){
    
            $jsonResponse = json_decode( $response, true);
  

            $production = $jsonResponse['production'] ?? null;
            $productionitems = $jsonResponse['productionitems'] ?? null;
       
            //dd($productionitems);
       
           return view('productions.show',['production' => $production ,  'productionitems' =>  $productionitems  ]);
         
       
    
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



   foreach ($productionitems as $productionitem){
      $jobCardItemId = $productionitem->jobcarditemId;

      //$sql = "qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding";
      $outstanding = DB::table('jobcarditems')
      ->selectRaw("qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding")
      ->where('id', $jobCardItemId)
      ->value('outstanding'); 

      $productionitem->outstanding = $outstanding;
   }    


   return view('productions.show',compact('production'));
} 





/**
* Show the form for editing the specified resource.
*
* @param  \App\Production  $Production
* @return \Illuminate\Http\Response
*/
public function edit(Production $production)
{
  
  $productionitems=DB::table('productionitems')->where('productionId', $production->id)
                                               ->orderBy('jobcarditemId','desc')->get();
   
     

   View::share('production', $production);
   View::share('productionitems',$productionitems);

   foreach ($productionitems as $productionitem){
              $jobCardItemId = $productionitem->jobcarditemId;


              $outstanding = DB::table('jobcarditems')
              ->selectRaw("qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding")
              ->where('id', $jobCardItemId)
              ->value('outstanding'); 

       
             
                  
              $productionitem->outstanding = $outstanding;
              

           

   }    
 
   return view('productions.edit',compact('production'));
}




/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\Production  $Production
* @return \Illuminate\Http\Response
*/



public function update(Request $request, $id)
{ 
   $myButton = $request->get('myButton');
   


   
 if ($myButton == "finish") {

   $production = Production::find($id);
   $production->stateId= 45;
   $production->save();
   
   $document = new DocumentAudit();
   $document->docId =  $id;
   $document->docType = 'productionItem'; 
   $document->stateId = $production->stateId;
   $document->other = $production->other;
   $document->userId = Auth::id();
   $document->action = 'Complete';
   $document->save();

   
   
   DB::table('productionitems')
                ->where('productionId',  $production->id)
                ->update(['stateId' => 45]);
   
   return redirect()->route('productions.index')
   ->with('success','Production sucessfully Completed');
   
   }

   else{

$production = Production::find($id);
$production->refNo = $request->refNo;
$production->other = $request->other;
$production->value = $request->value;
$production->processId = $request->processId;
$production->machineryId = $request->machineryId;
$production->employeeId = $request->employeeId;
$production->serialNo = $request->serialNo;
$production->userId = Auth::id();
$production->shiftId = $request->shiftId;
$production->stateId = 61;
$production->save();
return redirect()->route('productions.index')
->with('success','Production Has Been updated successfully');
}

}
/**
* Remove the specified resource from storage.
*
* @param  \App\Production  $Production
* @return \Illuminate\Http\Response
*/
public function destroy(Production $production)
{
   dd('hoyoo');
$production->delete();
return redirect()->route('productions.index')
->with('success','Production has been deleted successfully');
}


public function changestat(Request $request)
{

   
   
   
        // Validate the incoming request
        $request->validate([
         'productId' => 'required|integer',
         'productionId' => 'required|integer',
     ]);

     $productId = $request->get('productId');
     $productionId = $request->get('productionId');
  

     // Find the first item matching the criteria
     $productionItem = Productionitem::where('productionId', $productionId)
                                       ->where('productId', $productId)
                                       ->where('stateId', 44)
                                       ->where('qnt', 1)
                                       ->first();

if ($productionItem) {
//$productionItem->update(['stateId' => 134]);
DB::table('productionitems')->where('id',$productionItem->id)->update(['stateId'=> 134]);


return response()->json(['message' => 'State updated successfully.']);
} else {

return response()->json(['message' => 'No item found to update.'], 404);
}

   
 
   
   return response()->json(['message' => 'State updated successfully.']);
   


     }



public function changestate(Request $request)
{ 
   $id = $request->get('id');

   Productionitem::where('id', $id)
                  ->update(['stateId' => 134 ]);

      //$productionitems = DB::table('productionitems')->where('id',$id )->get();

   //    foreach ($productionitems as $productionitem) {
   //       if ($productionitem->jobcarditemId > 0) {
    

   // $jobcardId = DB::table('jobcarditems')
   //                ->where('id', $productionitem->jobcarditemId )
   //                ->value('jobCardId');

   //   $qnt     = DB::table('jobcarditems')   
   //                ->where('id', $productionitem->jobcarditemId )
   //                ->value('qnt');




   //  $finaltOTALI = $qnt + $productionitem->qnt;


   //    DB::table('jobcarditems')
   //          ->where('id', $productionitem->jobcarditemId  )
   //          ->update(['qnt' =>   $finaltOTALI]); 

   //    DB::table('job_cards')
   //       ->where('id', $jobcardId )
   //       ->update(['qnt' =>  $finaltOTALI ]); 

   

   //       }
   //   }
     
     
     
     
     
     
     

        
      // foreach ($productionitems as $productionitem){

      //    //dd('the drone');

  

        
     

      // $stocks=DB::table('stocks')->where('productId',$productionitem->productId)->get();
         
      // foreach ($stocks as $stock){
      //    $id = $stock->id;
      //    $prv = $stock->qnt;

      //    $Lowestunit=DB::table('porducts')->where('id',$productionitem->productId )->pluck('unitTypeId');

      //       $pack = DB::table('types')->where('id',$productionitem->unitId )->pluck('value');
      //       $packet = DB::table('types')->where('id',$Lowestunit )->pluck('value');

       

      //    //dd(''.);

      //      //echo "<pre>";
      //     // print_r($packet);
      //     // exit;

      //     $packValue = $pack[0] * $productionitem->qnt;
      //     $packetValue = $packet[0];

      //      $qntperpacket =  $packValue  / $packetValue ;


    

      

   
      //         $stocktrans = new StocksTrans();
      //         $stocktrans->stockId = $id;
      //         $stocktrans->userId = Auth::id();
      //         $stocktrans->docId= $productionitem->id;
      //         $stocktrans->docType= 135;
      //         $stocktrans->qnt = $qntperpacket;
      //         $stocktrans->save();
   
      //         Stock::where('id', $id)
      //                ->update(['qnt' =>$stock->qnt - $qntperpacket ,
      //                          'prvqnt' =>$stock->qnt - $qntperpacket ]);
   
            
      //                        }

      //                      }
    
        $response  = 67689;

    return response()->json($response);
}


public function generate(Request $request){


   $porduct = $request->get('product');

   $products =DB::table('porducts')->where('id', $porduct)->get();

   foreach( $products as  $product){

      $bagtype = $product->bagType;
      $unitId =  $product->unitTypeId;
      $weightper1000 = $product->WeightPerProduct;
      

   }

   

     

   $job_card = new JobCard;
   $job_card->refNo = $request->refNo;
   $job_card->description = $request->description;
   $job_card->startDate = date('Y-m-d');
   $job_card->barcode = Barcode::uniqidReal();
   $job_card->productId = $porduct;
   $job_card->unitId = $unitId;
   $job_card->noOfProcesses = 1;
   $job_card->qnt = 200;
   $job_card->weightper1000 = $weightper1000 ;
   $job_card->bagType = $bagtype;
   $job_card->customerId = 124;
   $job_card->other = $request->other;
   $job_card->stateId = 61;
   $job_card->userId = Auth::id();
   $job_card->jobcardType = 122; 
   $job_card->save();


   $jobcarditem = new Jobcarditem;
   $jobcarditem->name = Barcode::uniqidReal();
   $jobcarditem->jobCardId = $job_card->id;
   $jobcarditem->productId = $porduct ;
   $jobcarditem->bagType = $bagtype;
   $jobcarditem->barcode = Barcode::uniqidReal();
   $jobcarditem->other = $request->other;
   $jobcarditem->stateId = 61;
   $jobcarditem->processId = 24;
   $jobcarditem->qnt = $request->qnt;
   $jobcarditem->userId = Auth::id();
   $jobcarditem->unitId = $unitId;
   $jobcarditem->jobcardType = 122; 
   $jobcarditem->save();




   $response['data'] = $jobcarditem->id ;
 
   
 
   return response()->json($response);
  }




  Public function complete(Request $request)
  {


   $url = env('APP_URL');
   $maxRetries = 3; 
   $retryDelay = 2; 

      $production = $request->productionId;

   //    $response = Http::get($url . '/qryproduction/destroy?id=' . $production);

  
  
   //    if ($response->successful()) {
    
   //   //dd($response);

   //     return response()->json($response);
    
   //    } else {
          
   //        dd('Sorry , there an error with your request');
      
   //    }


      
   for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Make the HTTP request
          $response = Http::timeout(10) // Set a timeout of 10 seconds
                            ->retry(3, 1000) // Retry 3 times with a 1-second delay
                            ->getget($url . '/qryproduction/destroy?id=' . $production);
    
          //$data['info'] = json_decode($response, true);
      
          // Check if the request was successful
          if ($response->successful() ){
    
            return response()->json($response);
    
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

      

  


  public function srchworkspace(Request $request)
  {
      $user = $request->user;
      
      $today = Carbon::today()->toDateString();

      $response = [];
  
      $response = DB::table('workspaces')
                     ->where('userId', $user)
                     ->where('state', 1)
                     ->whereDate('created_at', Carbon::today())
                     ->value('productionId');

      // $workspace = Workspace::whereDate('created_at', $today)->where('state', 1)->where('userId', (int)$user)->();


          
  
  
      if (empty($response)) {
          $response = [0];
      }
        Log::info($response);
     // Log::info($workspace);
  
      return response()->json($response);
  }




  public function production(Request $request)
  {
      
   $shift  = $request->shift;
   $machine  = $request->machine;
   $process  = $request->process ;
   $user  = $request->user;

   
    

   

         // $production = new Production;
         // $production->refNo = 0;
         // $production->other = 0;
         // $production->value = 0;
         // $production->processId = $request->process;
         // $production->machineryId = $machine ;
         // $production->employeeId = $user;
         // $production->serialNo = SerialNo::generateSerialNumber();
         // $production->userId = Auth::id();
         // $production->stateId = 62;
         // $production->shiftId = $shift;
         // $production->prodDate = now();
         // $production->save();

         // $workspace = new Workspace;
         // $workspace->userId = $user;
         // $current_time = now();
         // $workspace->state = 1;
         // $workspace->startTime = $current_time;
         // $workspace->endTime = now();
         // $workspace->userId = Auth::id();
         // $workspace->productionId = $production->id;
         // $workspace->save();

         // dd('WOOOOOOOOOW');

         
    $data = [

      'refNo' => 0,
      'other' => 0,
      'value' => 0,
      'processId' => $request->process,
      'machineryId' => $machine ,
      'employeeId' =>  Auth::id(),
      'serialNo' => SerialNo::generateSerialNumber(),
      'userId' => Auth::id(),
      'stateId' => 62,
      'shiftId' =>  $shift,
     
  ];

  
  
  
  $url = env('APP_URL1');
  $maxRetries = 3; 
  $retryDelay = 2; 


  
 
  
//   $response = Http::get($url.'/qryproduction/store',$data);

  
  
  
//   if ($response->successful()) {

//    $jsonResponse = json_decode( $response, true);

//    return response()->json( $jsonResponse );

//   } else {
      
//       dd('Sorry , there an error with your request');
  
//   }



  
      
  for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
   try {
       // Make the HTTP request
       $response = Http::timeout(10) // Set a timeout of 10 seconds
                         ->retry(3, 1000) // Retry 3 times with a 1-second delay
                         ->get($url.'/qryproduction/store',$data);
 
       //$data['info'] = json_decode($response, true);
   
       // Check if the request was successful
       if ($response->successful() ){
 
         $jsonResponse = json_decode( $response, true);

         return response()->json( $jsonResponse );
 
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

//   public function productionitem(Request $request)
//   {
      
//    $product = $request->product;
//    $unit = $request->unit;
//    $qnt = $request->qnt;
//    $production = $request->production;
//    $jobcard = $request->jobcard;

  

//    $productionitem = new Productionitem;
//    $productionitem->productionId = $production;
//    $productionitem->jobcarditemId = $jobcard ;
//    $productionitem->other = 'none';
//    $productionitem->productId = $product;
//    $productionitem->qnt = $qnt;
//    $productionitem->unitId = $unit;
//    $productionitem->save();


//    $jsonArray =DB::table('productionitems')->where('productionId', $production)->get();
 
//    $productionitems = json_decode($jsonArray, true);

//    Log::info($productionitems);

//    return view('productionperemployees.create', compact('productionitems'));

//  }


  public function searchproduction(Request $request)
  {

   $product = $request->input('product');
   $jobcard = $request->input('jobcard');

   Log::info($product);
   Log::info($jobcard);

   Log::info('product jocard');

   if($product){


       $productInfo =DB::table('porducts')->where('id', $product)->get(['id','unitPackId']);
 
    }
    
    if($jobcard){
 
       $product =DB::table('jobcarditems')->where('id', $jobcard)->pluck('productId');
 
       $productInfo =DB::table('porducts')->where('id', $product)->get(['id','unitPackId']);
 
 
    }
 
 
    $response = $productInfo; 

    Log::info($response);
 
 
 
    return response()->json($response);
 
 
 
   }
 
}
