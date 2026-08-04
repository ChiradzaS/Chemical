<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Recycle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTime; 


class RestfulRecycletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $search = $request->input('search');
  
       
        if($search){

        

            
            $fromDate              =  $request->input('fromDate');
            $toDate                =  $request->input('toDate');
            $materialTypeId        =  $request->input('materialTypeId');
            $shiftId               =  $request->input('shiftId');
            $shiftComp             =  $request->input('shiftComp');
            $materialTypeIdComp    =  $request->input('materialTypeIdComp');
            $date  =  $request->input('date');




 



            $data = DB::table('recycles')
            ->select(
                'shiftId',
                'materialTypeId',
                DB::raw('DATE(DateComplete) as date'),
                DB::raw('SUM(kilos) as total_kilos')
            )
            
            ->where    ('materialTypeId',$materialTypeIdComp,$materialTypeId)
            ->where    ('shiftId',$shiftComp, $shiftId)
            ->whereDate ('DateComplete' , '>=', $date )
            ->whereDate ('DateComplete' , '<=',  $toDate ) 
           ->groupBy  ('shiftId', 'materialTypeId', DB::raw('DATE(DateComplete)'))
            ->orderBy  (DB::raw('DATE(DateComplete)'), 'desc') 

            ->get();
        
      
               
             return response()->json($data);
      


           
        
        }
      

       $data = DB::table('recycles')
       ->select(
           'shiftId',
           'materialTypeId',
           DB::raw('DATE(DateComplete) as date'),
           DB::raw('SUM(kilos) as total_kilos')
       )
       ->groupBy('shiftId', 'materialTypeId', DB::raw('DATE(DateComplete)'))
       ->orderBy(DB::raw('DATE(DateComplete)'), 'desc') // Sort by date in ascending order
       ->get();
   
 
          
        return response()->json($data);

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

        $unique_code     = $request->input('code');

        //Log::info('LLLLLLLLLL'.$unique_code);

        

        // $recycle = new Recycle;
        // $recycle->operator        = $request->input('operator');
        // $recycle->kilos           = $request->input('kilos');
        // $recycle->machineId       = $request->input('machineId');
        // $recycle->shiftId         = $request->input('shiftId');
        // $recycle->materialTypeId  = $request->input('materialTypeId');
        // $recycle->code     = $unique_code;
        // $recycle->save();

        $currentHour = now()->hour;
        $currentDate = now(); 

        $currentDateTime = new DateTime();
        $currentTime = $currentDateTime->format('H:i');

    
        if ($currentTime >= '00:00' && $currentTime <= '06:00') {
            $currentDate->modify('-1 day');
        }


        $shiftId = ($currentHour >= 6 && $currentHour < 18) ? 31 : 30;


        
        $recycle = new Recycle([


            'operator'       => $request->input('operator'),
            'kilos'          => $request->input('kilos'),
            'shiftId'        => $shiftId,
            'machineId'      => $request->input('machineId'),
            'materialTypeId' => $request->input('materialTypeId'),
            'code'           => $unique_code,
            'DateComplete'   => $currentDate


        ]);
        
        $recycle->save();

        $data = $recycle->id;

        return response()->json($data);
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
