<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\AllocationItem;
use Illuminate\Http\Request;
use App\Models\JobCard;
use App\Models\Jobcarditem ;
use DB;
use Auth;

class AllocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      
        $allpocation = new Allocation;
        $allocation->machineId = $request->input('machineId');
        $allocation->stateId  = $request->input('stateId')?? 'none';
        $allocation->save();


        
        $allocationitem = new AllocationItem;
        $allocationitem->allocationsId = $allocation->id;
        $allocationitem->jobCardId  = $request->input('jobCardId');
        $allocationitem->productId  = $request->input('productId');
        $allocationitem->customerId  = $request->input('customerId');
        $allocationitem->progress  = $request->input('progress') ?? 50 ;
        $allocationitem->stateId  = $request->input('stateId');
        $allocationitem->start  = $request->input('start');
        $allocationitem->end =$request->input('end');
        $allocationitem->save();
        
        
    
           $responds = $allocation->id ;



           return response()->json($responds);
        


        return view('allocation.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        
        $allpocation = new Allocation;
        $allocation->machineId = $request->input('machineId');
        $allocation->stateId  = $request->input('stateId')?? 'none';
        $allocation->save();


        
        $allocationitem = new AllocationItem;
        $allocationitem->allocationsId = $allocation->id;
        $allocationitem->jobCardId  = $request->input('jobCardId');
        $allocationitem->productId  = $request->input('productId');
        $allocationitem->customerId  = $request->input('customerId');
        $allocationitem->progress  = $request->input('progress') ?? 50 ;
        $allocationitem->stateId  = $request->input('stateId');
        $allocationitem->start  = $request->input('start');
        $allocationitem->end =$request->input('end');
        $allocationitem->save();
        
        
    
           $responds = $allocation->id ;



           return response()->json($responds);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        // $allpocation = new Allocation;
        // $allocation->machineId = $request->input('machineId');
        // $allocation->stateId  = $request->input('stateId')?? 'none';
        // $allocation->save();


        
        // $allocationitem = new AllocationItem;
        // $allocationitem->allocationsId = $allocation->id;
        // $allocationitem->jobCardId  = $request->input('jobCardId');
        // $allocationitem->productId  = $request->input('productId');
        // $allocationitem->customerId  = $request->input('customerId');
        // $allocationitem->progress  = $request->input('progress') ?? 50 ;
        // $allocationitem->stateId  = $request->input('stateId');
        // $allocationitem->start  = $request->input('start');
        // $allocationitem->end =$request->input('end');
        // $allocationitem->save();
        
        
    
           $responds = 10 ;



           return response()->json($responds);
       
        }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Allocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function show(Allocation $allocation)
    {
       //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Allocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function edit(Allocation $allocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Allocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       
        $allocation = Allocation::find($id);
        $allocation->jobcarditemId = $request->	jobcarditemId;
        $allocation->machineId = $request->machineId;
        $allocation->date = $request->date;
        $allocation->userId = $request->userId;
        $allocation->shiftId = $request->shiftId;
        $allocation->operator = $request->operator;
        $allocation->processId = $request->processId;
        
    //         echo "<pre>";
    //     print_r(  $allocation->processId);
    //    exit;
        $allocation->save();
  
          return redirect()->route('allocation.index')->with('success','You have just allocated a new jobcard to a machine.');
        }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Allocation  $allocation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Allocation $allocation)
    {
        //
    }

    

}
