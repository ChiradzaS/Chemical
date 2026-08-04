<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use App\Models\Orders;
use App\Models\Allocation;
use App\Models\AllocationItem;
use App\Models\JobCardItem;
use App\Models\Order_item;
use App\Models\Machinery;
use App\Models\Type;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTime; 
use DB;


class RestAllocationController extends Controller
{


    public function index(Request $request)
    {
        
        $username = $request->username;
        $password = $request->password;

        $allocationlist = $request->query('allocationlist');

        $yesterday = Carbon::yesterday();

           if ( $allocationlist) {

                    $results = DB::table('allocation_items')
                        ->select(
                            'allocation_items.machineId',
                            'allocation_items.id as item_id',
                            'allocation_items.jobCardId',
                            'allocation_items.progress',
                            'allocation_items.productId',
                            'allocation_items.customerId'
                        )
                  ->whereDate('allocation_items.created_at', '>=', $yesterday) // Get records from yesterday onwards
                    // OR if you want from today onwards:
                    // ->whereDate('allocation_items.start_date', '>=', Carbon::today())
                    ->orderBy('allocation_items.machineId')
                    ->orderBy('allocation_items.id')
                    ->get();

                    $machines = [];

                    foreach ($results as $row) {
                        $machineId = $row->machineId;
                        
                        if (!isset($machines[$machineId])) {
                            $machines[$machineId] = [
                                'id' => $machineId,
                                'name' =>  $machineId,
                                'jobCards' => []
                            ];
                        }
                        
                        $machines[$machineId]['jobCards'][] = [
                            'id' => $row->item_id,
                            'jobNumber' => $row->jobCardId,
                            'description' => $row->productId,
                            'customerId' => $row->customerId,
                            'start' => now(),
                            'end' => now()->addDays(2),
                            'progress' => (int)$row->progress,
                            'color' => $this->getJobColor($row->progress)
                        ];
                    }

    Log::info($machines);
    
    return array_values($machines);
    
  
    
    }


                            
        

        if ($username || $password) {

        // Find the user by username using Query Builder
        $user = DB::table('users')->where('name', $username)->first();
    
        // Check if user exists and password matches
        if ($user && $user->other === $password) {
            // Authentication passed
             $response = (int) $user->id;
            return response()->json($response);
        } else {
            // Authentication failed
            $response = 0;
            return response()->json($response);
        }

        
        }
    

        
        


       
        $id = DB::table('types')
                ->where('name' ,'=', 'customer')
                ->where('groupType' ,'=', 'user')
                ->value('id');
  
        $users = DB::table('users')
                   ->where('userType', $id)
                   ->orderBy('updated_at','asc')
                   ->get();
        


        $response = $users;

        //Log::info($response);

 
        return response()->json($response);   

    }


    private function getJobColor($progress)
{
    if ($progress >= 100) {
        return '#4f46e5'; // Blue for completed
    } elseif ($progress >= 75) {
        return '#0891b2'; // Cyan for high progress
    } elseif ($progress >= 50) {
        return '#16a34a'; // Green for medium progress
    } elseif ($progress >= 25) {
        return '#f59e0b'; // Yellow for low progress
    } else {
        return '#f38b89'; // Red for very low progress
    }
}




    public function store(Request $request)
    {

         $user = $request->user;
         $customer = $request->customer;

        Users::where('id', $user)->update(['company' => $customer]);

        $response = 88;
      
        return response()->json($response);
    }




    public function show(Request $request)
    {

    $dataString = $request->query('data');
    
    // Decode it back to array
    // Assuming $dataString is already defined and holds your encoded data
    $jobData = json_decode(urldecode($dataString), true);

    $machineId = $jobData['machinevalue'] ?? null;
    $jobCardId = $jobData['job_number'] ?? null;



    // $existingAllocation = AllocationItem::where('machineId', $machineId)
    // ->where('jobCardId', $jobCardId)
    // ->first();


    // // Only create new AllocationItem if no duplicate exists
    // if ($existingAllocation) {

        
    // return response()->json([
    // 'status' => 'success',
    // 'message' => 'Allocation and allocation items created successfully.',
    //        ]);


    // }

  

    // ---
    ## Saving the Allocation

    $allocation = new Allocation;
    $allocation->machineId  = $jobData['machinevalue'] ?? null;
    $allocation->stateId    = $request->input('stateId') ?? 61;
    $allocation->save();

    // ---
    ## Saving the AllocationItem
    
    $allocationitem = new AllocationItem;
    $allocationitem->allocationId  = $allocation->id; // Link to the newly created Allocation
    $allocationitem->machineId     = $jobData['machinevalue'] ?? null;
    $allocationitem->jobCardId     = $jobData['job_number']  ?? null; // Assuming jobCardId is in request
    $allocationitem->productId     = $jobData['productId'] ?? $request->input('productId'); // Prefer $jobData, fallback to request
    $allocationitem->customerId    = $jobData['customerId'] ?? $request->input('customerId'); // Prefer $jobData, fallback to request





        $calculatedProgress = 0; // Default progress

        if ($allocationitem->jobCardId) {
            // Fetch the job card item using the Eloquent model
            $jobCardItem = JobCardItem::where('id', $allocationitem->jobCardId)->first();

            if ($jobCardItem) {
                // Access properties directly from the model instance
                $totalQuantity =   $jobCardItem->outstanding; // Original total quantity
                $outstandingQuantity =  $jobCardItem->qnt; // Remaining quantity

                if ($totalQuantity > 0) {
                    // CORRECTED CALCULATION:
                    // Completed = Total - Outstanding
                    $completedQuantity = $totalQuantity - $outstandingQuantity;
                    
                    // Progress % = (Completed / Total) * 100
                    $calculatedProgress = ($completedQuantity / $totalQuantity) * 100;

                    // Ensure progress is within 0-100 bounds
                    $calculatedProgress = max(0, min(100, $calculatedProgress));
                } else {
                    // Handle case where total quantity is 0
                    // If outstanding is also 0, it means 100% complete (nothing to do, nothing outstanding)
                    // Otherwise, if outstanding > 0 but total is 0, it's an invalid state, default to 0
                    $calculatedProgress = ($outstandingQuantity == 0) ? 100 : 0;
                }
            } else {
                // Log or handle the case where the JobCardItem is not found
                \Log::warning("JobCardItem with ID '{$allocationitem->jobCardId}' not found for progress calculation.");
            }
        } else {
            // Log or handle the case where jobCardId is missing
            \Log::warning("Job Card ID is missing for progress calculation.");
        }


  
    // Determine stateId based on progress
            if ( $calculatedProgress > 0 ) {

                $stateId = 62;

            } else { // This covers 0 or any negative value, though progress is usually non-negative
                $stateId = 45;
            }


    $allocationitem->progress      = $calculatedProgress ?? 0;
    $allocationitem->stateId       = $stateId?? null;
    // Use Carbon to parse the date strings and then get only the date part
    $allocationitem->end = substr($jobData['end_date'], 0, 10);
    $allocationitem->start = substr($jobData['start_date'], 0, 10);
    $allocationitem->save();


    return response()->json([
    'status' => 'success',
    'message' => 'Allocation and allocation items created successfully.',
           ]);


            
    }




    public function update(Request $request)
    {
        $userId = $request->get('user');

        DB::table('users')
            ->where('id',$userId )
            ->update(['company' => null]);


            $response = 1;
                
        return response()->json( $response);
    }




    public function destroy(Request $request)
    {


            $dataString = $request->query('data');
            $allocationData= json_decode(urldecode($dataString), true);

         


            $action  = $allocationData['action'];
            $id           = $allocationData['id'];





    // const replacementData = {
    //   action: 'replace',
    //   job_number: jobNumber,
    //   machinevalue: machinevalue,
    //   start_date: startDates || today,
    //   end_date: endDates || today,
    //   customerId: customerId,
    //   productId: productId,
    // };

            if($action == 'replace' ){

                $jobNumber    = $allocationData['jobNumber'];
                $customerId   = $allocationData['customerId'];
                $productId    = $allocationData['productId'];

                
        $calculatedProgress = 0; // Default progress

        if ($allocationitem->jobCardId) {
            // Fetch the job card item using the Eloquent model
            $jobCardItem = JobCardItem::where('id', $jobNumber)->first();

            if ($jobCardItem) {
                // Access properties directly from the model instance
                $totalQuantity =   $jobCardItem->outstanding; // Original total quantity
                $outstandingQuantity =  $jobCardItem->qnt; // Remaining quantity

                if ($totalQuantity > 0) {
                    // CORRECTED CALCULATION:
                    // Completed = Total - Outstanding
                    $completedQuantity = $totalQuantity - $outstandingQuantity;
                    
                    // Progress % = (Completed / Total) * 100
                    $calculatedProgress = ($completedQuantity / $totalQuantity) * 100;

                    // Ensure progress is within 0-100 bounds
                    $calculatedProgress = max(0, min(100, $calculatedProgress));
                } else {
                    // Handle case where total quantity is 0
                    // If outstanding is also 0, it means 100% complete (nothing to do, nothing outstanding)
                    // Otherwise, if outstanding > 0 but total is 0, it's an invalid state, default to 0
                    $calculatedProgress = ($outstandingQuantity == 0) ? 100 : 0;
                }
            } else {
                // Log or handle the case where the JobCardItem is not found
                \Log::warning("JobCardItem with ID '{$allocationitem->jobCardId}' not found for progress calculation.");
            }
        } else {
            // Log or handle the case where jobCardId is missing
            \Log::warning("Job Card ID is missing for progress calculation.");
        }
             
        



            AllocationItem::where( 'id', $id )

                        ->update([

                            'jobCardId'    => $jobNumber, // New value for 'price'
                            'customerId'   => $customerId, // New value for 'price'
                            'productId'    => $productId , 
                            'progress'     =>  $calculatedProgress,     // New value for 'stock'

                        ]);




            
                return response()->json([
                'status' => 'success',
                'message' => 'Allocation and allocation items created successfully.',
                    ]);




               }


           

                        AllocationItem::where('id', $id)->forceDelete();



                
            
                    return response()->json([
                    'status' => 'success',
                    'message' => 'Allocation and allocation items created successfully.',
                        ]);


    }

    
}


