<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AllocationProduction;
use App\Models\Production;
use App\Models\Productionitem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;

class ProductionAllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

      //~dd('10');


   $itemIdToDelete = $request->query('delete');
   //$itemIdToproductionDelete = $request->query('deleteinproduction');

   if ($itemIdToDelete) {

      Productionitem::where('id', $itemIdToDelete)
                    ->update(['stateId' => 134 ]);
       // Your delete logic goes here
   }



  

   $url = env('APP_URL1');
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
   $operator = $request->get('operatorId');


   if ($action <> null && trim($action, ' ') == 'previous') {


    $fromDate = $request->get('fromDate');

    if (is_null($fromDate)) {
      // If $fromDate is null, set it to a date three months prior to today
      $fromDate = Carbon::today()->subMonths(3)->toDateString();
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

    
    $operatorComp = '<>';
    if ($operator > 0) {
       $operatorComp = '=';
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
       'previous'   =>   10, 
       'date' => $fromDate,
       'operator' => $operatorComp,
       'operatorId' => $operator,

    ];

    //dd($data);



    

  //   $response = Http::get($url.'/qryoperatorproduction/index?data='.http_build_query($data));



  //   if ($response->successful()) {


  //      $data['productions'] = json_decode($response->body(), true);

  //      return view('allocateproductions.index', $data , [ 'operatorId' => -9,'machineryId' => -9, 'shiftId' => -9,'processId' => -9 ,'toDate' => $toDate , 'fromDate'=> $fromDate]); 
    
  //   } else {
        
  //       dd('Sorry , there an error with your request');
    
  //   }          
    
    
    

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
          // Make the HTTP request
          $response = Http::timeout(10) // Set a timeout of 10 seconds
                            ->retry(3, 1000) // Retry 3 times with a 1-second delay
                            ->get($url.'/qryoperatorproduction/index?data='.http_build_query($data));
    
          //$data['info'] = json_decode($response, true);
      
          // Check if the request was successful
          if ($response->successful() ){
    
             

              $data['productions'] = json_decode($response->body(), true);

              return view('allocateproductions.index', $data , [ 'operatorId' => -9,'machineryId' => -9, 'shiftId' => -9,'processId' => -9 ,'toDate' => $toDate , 'fromDate'=> $fromDate]); 
           
    
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


      $fromDate = $request->get('fromDate');

      if (is_null($fromDate)) {
        // If $fromDate is null, set it to a date three months prior to today
        $fromDate = Carbon::today()->subMonths(3)->toDateString();
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

      
      $operatorComp = '<>';
      if ($operator > 0) {
         $operatorComp = '=';
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
         'operator' => $operatorComp,
         'operatorId' => $operator,

      ];

      //dd($data);



      

    //   $response = Http::get($url.'/qryoperatorproduction/index?data='.http_build_query($data));
 
 
 
    //   if ($response->successful()) {

 
    //      $data['productions'] = json_decode($response->body(), true);

    //      return view('allocateproductions.index', $data , [ 'operatorId' => -9,'machineryId' => -9, 'shiftId' => -9,'processId' => -9 ,'toDate' => $toDate , 'fromDate'=> $fromDate]); 
      
    //   } else {
          
    //       dd('Sorry , there an error with your request');
      
    //   }          
      
      
      

      for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Make the HTTP request
            $response = Http::timeout(10) // Set a timeout of 10 seconds
                              ->retry(3, 1000) // Retry 3 times with a 1-second delay
                              ->get($url.'/qryoperatorproduction/index?data='.http_build_query($data));
      
            //$data['info'] = json_decode($response, true);
        
            // Check if the request was successful
            if ($response->successful() ){
      
               
 
                $data['productions'] = json_decode($response->body(), true);

                return view('allocateproductions.index', $data , [ 'operatorId' => -9,'machineryId' => -9, 'shiftId' => -9,'processId' => -9 ,'toDate' => $toDate , 'fromDate'=> $fromDate]); 
             
      
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
   

//    $response = Http::get($url.'/qryoperatorproduction/index');


//    //dd('hoyooooo');
  
  
//    if ($response->successful()) {


      
//       $data['productions'] = json_decode($response->body(), true);

//       $toDate = '';
//       $fromDte = '';

//       return view('allocateproductions.index', $data, [ 'operatorId' => -9, 'machineryId' => -9, 'shiftId' => -9,'processId' => -9,'toDate' => $toDate , 'fromDate'=> $fromDate]);
      
//   } else {
       
//        dd('Sorry , there an error with your request');
   


//    }



   for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Make the HTTP request
        $response = Http::timeout(10) // Set a timeout of 10 seconds
                          ->retry(3, 1000) // Retry 3 times with a 1-second delay
                          ->get($url.'/qryoperatorproduction/index');
  
        //$data['info'] = json_decode($response, true);
    
        // Check if the request was successful
        if ($response->successful() ){
  
           

       
            $data['productions'] = json_decode($response->body(), true);

            $toDate = '';
            $fromDte = '';
      
            return view('allocateproductions.index', $data, [ 'operatorId' => -9, 'machineryId' => -9, 'shiftId' => -9,'processId' => -9,'toDate' => $toDate , 'fromDate'=> $fromDate]);
            
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

      $createdAtRaw = $request->created_at; // e.g., '2025-04-29'
      $createdAt = Carbon::parse($createdAtRaw)->startOfDay();
      $userId = $request->user_id;
      $shiftId = $request->shift_id;

      $productionId = $request->production_id;
      $product      = $request->product;
  

      
      // Define time range based on shift ID
      if ($shiftId == 31) {
          // Day shift: 6 AM to 6 PM on the same day
          $fromDateTime = $createdAt->copy()->setTime(6, 0, 0);
          $toDateTime = $createdAt->copy()->setTime(18, 0, 0);
      } elseif ($shiftId == 30) {
          // Night shift: 6 PM on the given day to 6 AM the next day
          $fromDateTime = $createdAt->copy()->setTime(18, 0, 0);
          $toDateTime = $createdAt->copy()->addDay()->setTime(6, 0, 0);

      } else {
        
          return response()->json(['error' => 'Invalid shift ID'], 400);
          
      }
      
      // Format prodDate to match the DB format (Y-m-d)
      $prodDate = $createdAt->format('Y-m-d');
      
      // Query production items
      $items = Productionitem::where('userId', $userId)
                              //->where('shiftId', $shiftId)
                              ->where('productionId', $productionId)
                              ->where('productId', $product)
                              //->whereBetween('created_at', [$fromDateTime, $toDateTime])
                              ->get();

                  //Log::info($items);




     return view('allocateproductions.create', compact('items', 'prodDate', 'shiftId', 'userId'));
      
      


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        dd('hoyooooooo');
  

    $useralllocation = new AllocationProduction;
    $useralllocation ->userId = $request->userId;
    $useralllocation ->machineId = $request->machineId;
    $useralllocation ->shiftId = $request->shiftId;
    $useralllocation ->save();
    return redirect()->route('allocateproductions.index')->with('success','Company has been created successfully.');

        
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        //$company->delete();

        AllocationProduction::truncate();
        
        return redirect()->route('allocateproductions.index')
        ->with('success','allocateproductions has been deleted successfully');
        
    }
}
