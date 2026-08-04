<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Allocation;
use Illuminate\Support\Facades\Log;
use DB;
use Auth;

class listController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $action = $request->get('action');



      
   
      
    
    if( $action <> null ){
    
    
      $jobcarditemId = $request->get('jobcarditem');
          
      $jobcardId = $request->get('jobcard');
      
    
    


          
    $jobcardidComp = '<>';
    if ($jobcardId > 0) {

        $jobcarditems = DB::table('jobcarditems')
                       ->where('jobCardId', $jobcardId)
                       ->pluck('id');
                    
                       
                        $data['allocations'] = Allocation::whereIn('jobcarditemId', $jobcarditems)->get();         

                        return  view('lists.index',$data,['processId'=> -9,'shiftId' => -9,'machineId' => -9, 'materialTypeId' => -9 ]); 


       
    }
    
    
    $jobcarditemComp = '<>';
        if ($jobcarditemId > 0) {
           $jobcarditemComp = 'like';
        }
    
    
    
        
    
        
      
             
        
        
    
      $data['allocations'] = Allocation::where('jobcarditemId', ''.$jobcarditemComp,'%'.$jobcarditemId.'%')                                  
                                        ->orderBy('id','desc')->paginate(500);
    
      return  view('lists.index', $data ,['processId'=> -9,'shiftId' => -9,'machineId' => -9, 'materialTypeId' => -9 ]);                               
    }
    
    
            $allocations= DB::table('allocations')->get();
    
            
                    // echo "<pre>";
                    // print_r( $allocations);
                    // exit;
    
    
    
    return view('lists.index',['allocations' =>$allocations ,'processId'=> -9,'shiftId' => -9,'machineId' => -9, 'materialTypeId' => -9] );
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
