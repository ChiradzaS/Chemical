<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Library\SerialNo;
use App\Models\Production;
use App\Models\Jobcarditem;
use App\Models\Stock;
use App\Models\StocksTrans;
use App\Models\Jobcard;
use App\Models\Workspace;
use App\Models\Productionitem;
use App\Models\DocumentAudit;
use Illuminate\Support\Facades\View;
use App\Barcode\Barcode;
use DB;
use Carbon\Carbon;
use DateTime;



class UserRestController extends Controller
{
    
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request) 
    {
        $user = new User;
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->cellPhone = $request->cellPhone;
        $user->telephone= $request->telephone;
        $user->userTypeId= $request->userTypeId;
        $user->emailAddress = $request->emailAddress;
        $user->userPosition= $request->userPosition;
        $user->userName= $request->userName;
        $user->password = $request->password;
        $user->securityLevel = $request->securityLevel;
        $user->other = $request->other;
        $user->userId = $request->userId;
        $user->save();
        return response()->json($user, 201);
    }

    public function qryEmployee(Request $request)
    {

        $tmpId = $request->get('id');
        $employee = Employee::find($tmpId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found in database.'], 404);
        }
        return response()->json($employee); 

    }

    public function qryUser(Request $request)
    { 
        $tmpId = $request->get('id');
        $machineId = $request->get('machineId');
       




        if ($machineId != 0 && $tmpId ) {

            $thresholdTime = Carbon::now()->subHours(8);

          
            Production::whereDate('created_at', '<', $thresholdTime)
                                       ->update(['stateId' => 45]);
   
         
           $production = new Production;
           $production->refNo = 0;
           $production->other = 0;
           $production->value = 0;
           $production->processId = $request->input('processId');
           $production->machineryId = $request->input('machineryId');
           $production->userId = $request->input('userId');
           $production->employeeId = $request->input('userId');
           $production->stateId = 62;
   
           $current_time = Carbon::now();
           $morning_shift_start = Carbon::createFromTime(6, 0, 0);
           $evening_shift_start = Carbon::createFromTime(18, 0, 0);
   
           if ($current_time->between($morning_shift_start, $evening_shift_start)) {
               $production->shiftId = 31;
           } else {
               $production->shiftId = 30; 
           }
   
       
        
           $production->prodDate = now();
           $production->startTime = date('H:i:s');
   
   
   
   
           $id = $request->input('jobcard');
   
           $parts = explode('-', $id);
           if (count($parts) >= 2) {
   
               $id = $parts[0];
               $secondnumber = $parts[1];
               
           }
   
        
   
   
   
   
   
           
           $production->serialNo = SerialNo::generateSerialNumber();
           
   
           
           $production->save();




        }
        

       
        // $processId    = $request->get('processId');
        // $machineId    = $request->get('machineId');
        // $productionId = $request->get('productionId');
       
        $user = User::find($tmpId);
        if (!$user) {
            return response()->json(['error' => 'User not found in database.'], 404);
        }

        return response()->json($user);  
    } 

    /*
    public function show(Request $request, $id)
    {
        $tmpId = $request->get('id');
        $user = User::find($tmpId);
        if (!$user) {
            return response()->json(['error' => 'Method show not find user in list'], 404);
        }
        return response()->json($user);
    }
    */
    

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->cellPhone = $request->cellPhone;
        $user->telephone= $request->telephone;
        $user->userTypeId= $request->userTypeId;
        $user->emailAddress = $request->emailAddress;
        $user->userPosition= $request->userPosition;
        $user->userName= $request->userName;
        $user->password = $request->password;
        $user->securityLevel = $request->securityLevel;
        $user->other = $request->other;
        $user->save();
        return response()->json($user);
    }

    public function destroy(Request $request, $id)
    {
        $tmpId = $request->get('id');
        $user = User::find($tmpId);
        if (!$user) {
            return response()->json(['error' => 'User not found to delete'], 404);
        }
        //$user->delete();
        //return response()->json(['error' => 'User deleted'], 204);
        return response()->json($user);
    }

}
