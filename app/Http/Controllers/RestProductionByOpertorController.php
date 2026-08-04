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

class RestProductionByOpertorController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        $previous = $request->input('previous');

        // Whole current month instead of a single day
        $startTim = Carbon::now()->startOfMonth();
        $endTim   = Carbon::now()->endOfMonth();


        if ($previous) {
            // Get current date and time
            $currentDate = Carbon::now();
            $currentHour = $currentDate->hour;

            // Determine shift (Day: 6 AM - 6 PM, Night: 6 PM - 6 AM)
            if ($currentHour >= 6 && $currentHour < 18) {
                // Currently in the Day Shift (6 AM to 6 PM)
                // Get previous shift (Night Shift: 6 PM to 6 AM on the previous day)
                $fromDate = $currentDate->copy()->subDay()->setTime(18, 0, 0); // Previous day's 6 PM
                $toDate = $currentDate->copy()->subDay()->setTime(6, 0, 0); // Previous day's 6 AM
                $shiftId = 31; // Night shift ID
            } else {
                // Currently in the Night Shift (6 PM to 6 AM)
                // Get previous shift (Day Shift: 6 AM to 6 PM on the same day)
                $fromDate = $currentDate->copy()->setTime(6, 0, 0); // Same day's 6 AM
                $toDate = $currentDate->copy()->setTime(18, 0, 0); // Same day's 6 PM
                $shiftId = 30; // Day shift ID
            }


            // Query to fetch production items from the previous shift
            $response = Productionitem::select(
                'id as item_id',
                'productId as item_productId',
                'qnt as item_qnt',
                'employeeId as item_employeeId',
                'processId as item_processId',
                'machineId as item_machineId',
                'shiftId as item_shiftId',
                'weightState as item_weightState',
                'weight as item_weight',
                'unitId as item_unitId',
                'created_at as item_created_at',
                'jobcarditemId as item_jobcarditemId',
                'weight_per_bale as item_weight_per_bale',
                'productionId as item_productionId'
            )
            ->where('shiftId', $shiftId)
            ->where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $toDate)
            ->where('stateId', '<>', 134)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json($response);
        }


        if ($search) {

            // Assume these values come from the request
            $fromDate = Carbon::parse($request->input('date'));
            $toDate = Carbon::parse($request->input('toDate'));
            $action = $request->input('action');
            $machineryId = $request->input('machineryId');
            $shiftId = $request->input('shiftId');
            $processId = $request->input('processId');
            $employeeId = $request->input('employeeId');
            $shiftComp = $request->input('shiftComp');
            $processComp = $request->input('processComp');
            $machineryComp = $request->input('machineryComp');
            $job = $request->input('jobcardId');
            $jobComp = $request->input('jobComp');
            $operatorComp = $request->input('operator');
            $operator = $request->input('operatorId');

            $fromDate = Carbon::parse($request->input('date'))->startOfDay();  // Start of the day
            $toDate = Carbon::parse($request->input('toDate'))->endOfDay();    // End of the day

            DB::enableQueryLog();

            $response = Productionitem::select(
                'id as item_id',
                'productId as item_productId',
                'qnt as item_qnt',
                'employeeId as  item_employeeId',
                'processId as  item_processId',
                'machineId as item_machineId',
                'shiftId as item_shiftId',
                'weightState as item_weightState',
                'weight as item_weight',
                'unitId as item_unitId',
                'created_at as item_created_at',
                'jobcarditemId as item_jobcarditemId',
                'weight_per_bale as item_weight_per_bale',
                'productionId as item_productionId'
            )
                ->where('machineId', $machineryComp, $machineryId)
                ->where('processId', $processComp, $processId)
                ->where('shiftId', $shiftComp, $shiftId)
                ->where('userId', $operatorComp, $operator)
                ->where('created_at', '>=', $fromDate)
                ->where('created_at', '<=', $toDate)
                ->where('stateId', '<>', 134)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info(DB::getQueryLog());

            return response()->json($response);
        }


        // Default: whole current month
        $response = Productionitem::select(
            'id as item_id',
            'productId as item_productId',
            'qnt as item_qnt',
            'employeeId as  item_employeeId',
            'processId as  item_processId',
            'machineId as item_machineId',
            'shiftId as item_shiftId',
            'weightState as item_weightState',
            'weight as item_weight',
            'unitId as item_unitId',
            'created_at as item_created_at',
            'jobcarditemId as item_jobcarditemId',
            'weight_per_bale as item_weight_per_bale',
            'productionId as item_productionId'
        )
        ->where('stateId', '<>', 134)
        ->whereBetween('created_at', [$startTim, $endTim])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($response);
    }

}