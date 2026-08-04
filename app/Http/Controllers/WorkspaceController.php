<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Production;
use App\Models\Productionitem;
use DB;
use ProductionRptList;
use Auth;
use App\Models\Jobcarditem;
use App\Models\Stock;
use App\Models\StocksTrans;
use App\Models\Jobcard;
use App\Models\Workspace;
use App\Models\DocumentAudit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use App\Library\SerialNo;
use App\Barcode\Barcode;
use Carbon\Carbon;
use DateTime;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    
       $url = env('APP_URL1');
    
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
    
    
    
          
    
          $response = Http::get($url.'/qryproduction/index?data='.http_build_query($data));
     
     
     
          if ($response->successful()) {
    
     
             $data['productions'] = json_decode($response->body(), true);
    
             return view('workspaces.index', $data , [ 'machineryId' => -9, 'shiftId' => -9,'processId' => -9 ,'toDate' => $toDate , 'fromDate'=> $fromDate]); 
          
          } else {
              
              dd('Sorry , there an error with your request');
          
          }                                
         
       }
       
    
       $response = Http::get($url.'/qryproduction/index');
      
      
       if ($response->successful()) {
    
    
          
          $data['productions'] = json_decode($response->body(), true);
    
          $toDate = '';
          $fromDte = '';
    
          return view('workspaces.index', $data, [ 'machineryId' => -9, 'shiftId' => -9,'processId' => -9,'toDate' => $toDate , 'fromDate'=> $fromDate]);
          
      } else {
           
           dd('Sorry , there an error with your request');
       
       }
    
    
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }
}
