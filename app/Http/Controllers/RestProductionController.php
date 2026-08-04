<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Jobcarditem;
use App\Models\Machinery;
use App\Models\Stock;
use App\Models\StocksTrans;
use App\Models\Jobcard;
use App\Models\Workspace;
use App\Models\Type;
use App\Models\Productionitem;
use App\Models\DocumentAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Library\SerialNo;
use App\Barcode\Barcode;
use DB;
use Auth;
use Carbon\Carbon;
use DateTime;

class RestProductionController extends Controller
{
    
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $today = Carbon::now()->toDateString();
        $threeDaysAgo = Carbon::now()->subDays(1)->toDateString();


        $StartTimenight = Type::where('groupType', 'shift')->where('name', 'night')->value('start_time');
        $EndTimenight   = Type::where('groupType', 'shift')->where('name', 'night')->value('end_time');
        $StartTimeday   = Type::where('groupType', 'shift')->where('name', 'day')->value('start_time');
        $EndTimeday     = Type::where('groupType', 'shift')->where('name', 'day')->value('end_time');


        $startTim = Carbon::parse($today)->setTime(6, 0, 0); // 6 AM on $fromDate

        // End time: 6 AM on the following date
        $endTim = $startTim->copy()->addDay();


        


        if($search){

        

    // Parse input dates
    $fromDate = Carbon::parse($request->input('date')); // Assuming 'date' is the start date of the shift
    $toDate = Carbon::parse($request->input('toDate')); // Optional, if needed for other logic

    // Calculate the correct date range for the night shift (6 PM to 6 AM)
    $startTime = $fromDate->copy()->setTime(18, 0, 0); // 6 PM on $fromDate
    $endTime = $startTime->copy()->addHours(12); // 6 AM the next day

    // Build the query
    $response = Production::join('productionitems', 'productions.id', '=', 'productionitems.productionId')
        ->select(
            'productions.id as production_id',
            'productions.processId as production_processId',
            'productions.userId as production_userId',
            'productions.shiftId as production_shiftId',
            'productions.created_at as production_created_at',
            'productions.machineryId as production_machineryId',
            'productionitems.id as item_id',
            'productionitems.productId as item_productId',
            'productionitems.qnt as item_qnt',
            'productionitems.weightState as item_weightState',
            'productionitems.weight as item_weight',
            'productionitems.created_at as item_created_at',
            'productionitems.unitId as item_unitId',
            'productionitems.jobcarditemId as item_jobcarditemId',
            'productionitems.stateId as item_stateId',
            'productionitems.weight_per_bale as item_weight_per_bale'
        )
        
        ->where('productions.machineryId', $request->input('machineryComp'), $request->input('machineryId'))
        ->where('productions.processId', $request->input('processComp'), $request->input('processId'))
        ->where('productions.shiftId', $request->input('shiftComp'), $request->input('shiftId'))
        ->whereBetween('productions.created_at', [$startTime, $endTime]) // Filter by the correct shift duration
        ->where('productionitems.stateId', '<>', 134)
        ->orderBy('productions.userId', 'asc')
        ->orderBy('productions.shiftId', 'desc')
        ->get();

    // Organize data
    $machines = [];
    foreach ($response as $record) {
        $machines[$record->production_machineryId]['productions'][$record->production_id]['details'] = [
            'production_id' => $record->production_id,
            'processId' => $record->production_processId,
            'userId' => $record->production_userId,
            'shiftId' => $record->production_shiftId,
            'created_at' => $record->production_created_at
        ];

        $machines[$record->production_machineryId]['productions'][$record->production_id]['items'][] = [
            'item_id' => $record->item_id,
            'productId' => $record->item_productId,
            'weightState' => $record->item_weightState,
            'weight' => $record->item_weight,
            'quantity' => $record->item_qnt,
            'unitId' => $record->item_unitId,
            'jobcarditemId' => $record->item_jobcarditemId,
            'created_at' => $record->item_created_at,
            'weight_per_bale' => $record->item_weight_per_bale
        ];
    }

    

       
        $response = $machines;

        dd($query->toSql(), $query->getBindings());

        return response()->json($response);   
        
        
        }

        




     
   
        
        $response = Production::join('productionitems', 'productions.id', '=', 'productionitems.productionId')
            ->select(
                
                'productions.id as production_id',
                'productions.processId as production_processId',
                'productions.userId as production_userId',
                'productions.shiftId as production_shiftId',
                'productions.created_at as production_created_at',
                'productions.machineryId as production_machineryId',
                'productionitems.id as item_id',
                'productionitems.productId as item_productId',
                'productionitems.qnt as item_qnt',
                'productionitems.weightState as item_weightState',
                'productionitems.weight as item_weight',
                'productionitems.unitId as item_unitId',
                'productionitems.created_at as item_created_at',
                'productionitems.jobcarditemId as item_jobcarditemId',
                'productionitems.weight_per_bale as item_weight_per_bale'
            )
            // Query for productions between 6 AM today and midnight



            ->whereBetween('productions.created_at', [$startTim, $endTim])
        
            ->where('productionitems.stateId', '<>', 134)

        
            ->orderBy('productions.userId', 'asc')
            ->orderBy('productions.shiftId', 'desc')
            ->get();
        
    $machines = [];
    
    foreach ($response as $record) {
        $machines[$record->production_machineryId]['productions'][$record->production_id]['details'] = [
            'production_id' => $record->production_id,
            'processId' => $record->production_processId,
            'userId' => $record->production_userId,
            'shiftId' => $record->production_shiftId,
            'created_at' => $record->production_created_at
        ];
    
        // Check if the shiftId is 24 before adding items
        if ($record->production_shiftId == 30) {
            // Here you could further refine based on the created_at condition if needed
            $machines[$record->production_machineryId]['productions'][$record->production_id]['items'][] = [
                'item_id' => $record->item_id,
                'productId' => $record->item_productId,
                'weightState' => $record->item_weightState,
                'weight' => $record->item_weight,
                'quantity' => $record->item_qnt,
                'unitId' => $record->item_unitId,
                'jobcarditemId' => $record->item_jobcarditemId,
                'created_at' => $record->item_created_at,
                'weight_per_bale' => $record->item_weight_per_bale
            ];
        } else {
            
            // For other shift IDs, add items as usual
            $machines[$record->production_machineryId]['productions'][$record->production_id]['items'][] = [
                'item_id' => $record->item_id,
                'productId' => $record->item_productId,
                'weightState' => $record->item_weightState,
                'weight' => $record->item_weight,
                'quantity' => $record->item_qnt,
                'unitId' => $record->item_unitId,
                'jobcarditemId' => $record->item_jobcarditemId,
                'created_at' => $record->item_created_at,
                'weight_per_bale' => $record->item_weight_per_bale
            ];
        }
    }
    
     //Log::info($machines);
       
        $response = $machines;


        dd($query->toSql(), $query->getBindings());

        


 
        return response()->json($response);   

    }


    public function show(Request $request)
    {

        $productionId = $request->input('id');


        
             $productionitems = Productionitem::select('*')->where('productionId', $productionId)->get();    
             $production = Production::select('*')->where('id', $productionId)->first();


             $response = [

                'productionitems' => $productionitems,
                'production' => $production,
        
               ];



               return response()->json($response);   


                                

    }

public function store(Request $request)
{
    Log::info('STORE: entered', ['payload' => $request->all()]);

    try {

        $thresholdTime = Carbon::now()->subHours(8);
        Log::info('STORE: about to bulk-update stale production rows', ['thresholdTime' => $thresholdTime->toDateTimeString()]);

        Production::whereDate('created_at', '<', $thresholdTime)
            ->update(['stateId' => 45]);

        Log::info('STORE: stale production update done');

        $machinery = Machinery::find($request->input('machineryId'));
        Log::info('STORE: machinery lookup done', ['machineryId' => $request->input('machineryId'), 'found' => (bool) $machinery]);

        if ($machinery) {
            $processId = $machinery->processId;
        } else {
            $processId = $request->input('processId');
        }
        Log::info('STORE: processId resolved', ['processId' => $processId]);

        $production = new Production;
        $production->refNo = 0;
        $production->other = 0;
        $production->value = 0;
        $production->processId = $processId;
        $production->machineryId = $request->input('machineryId');
        $production->userId = $request->input('userId');
        $production->employeeId = $request->input('userId');
        $production->stateId = 62;

        Log::info('STORE: base fields set');

        $current_time = Carbon::now();
        $morning_shift_start = Carbon::createFromTime(6, 0, 0);
        $evening_shift_start = Carbon::createFromTime(18, 0, 0);

        if ($current_time->between($morning_shift_start, $evening_shift_start)) {
            $production->shiftId = 31;
        } else {
            $production->shiftId = 30;
        }
        Log::info('STORE: shift resolved', ['shiftId' => $production->shiftId, 'current_time' => $current_time->toTimeString()]);

        $production->prodDate = now();
        $production->startTime = date('H:i:s');

        $id = $request->input('jobcard');
        Log::info('STORE: raw jobcard input', ['jobcard' => $id]);

        $parts = explode('-', $id);
        if (count($parts) >= 2) {
            $id = $parts[0];
            $secondnumber = $parts[1];
        }

        $production->currentJobcard = $id;
        Log::info('STORE: about to save', ['production' => $production->toArray()]);

        $production->save();

        Log::info('STORE: save successful', ['productionId' => $production->id]);

        $response = $production;

        return response($response);

    } catch (\Throwable $e) {
        Log::error('STORE: exception caught', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response(['error' => 'Failed to create production record', 'message' => $e->getMessage()], 500);
    }
}


    public function destroy(Request $request)
    {

        $productionId = $request->input('id');


        $workspace = Workspace::where('productionId',$productionId)->first();

        if ($workspace) {
        
            $workspace->state = 0;  
            $workspace->endtime =now(); 
            $workspace->save();

        }


        Production::where('id', $productionId)
                    ->update(['stateId' => 45]);


         $response = 00;


        
    return response()->json($response);   

    }
                                

    
    public function update(Request $request)
    {
      

        $product = $request->input('product');
        $jobcard = $request->input('jobcard');


        if($product){


            $productInfo =DB::table('porducts')->where('id', $product)->get(['id','unitPackId']);
      
         }
         
         if($jobcard){
      
            $product =DB::table('jobcarditems')->where('id', $jobcard)->pluck('productId');
      
            $productInfo =DB::table('porducts')->where('id', $product)->get(['id','unitPackId']);
      
      
         }
      
      
         $response = $productInfo; 

        // Log::info($response);
      
      
      
         return response()->json($response);
      
        }

        public function newstore(Request $request){


            Log::info('OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO');

        }

        


    

}
