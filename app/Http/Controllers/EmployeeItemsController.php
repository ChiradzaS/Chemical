<?php

namespace App\Http\Controllers;

use App\Models\Jobcarditem;
use App\Models\Productionitem;
use Illuminate\Http\Request;
use App\Models\Porduct;
use App\Models\DocumentAudit;
use App\Models\Oder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class EmployeeItemsController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    
    public function index()
    {
      $data['employeeitems'] = Productionitems::orderBy('id','desc')->paginate(50);
      return view('employeeitems.index', $data);
    }
    
    
    
    /**
      * Show the form for creating a new resource.
      *
      * @return \Illuminate\Http\Response
      */
      public function create(Request $request)
      {
        $employee= $request->get(''.Auth::id());
   



    $myButton= $request->get('myButton');
  

    $productionId = $request->get('productionId');
    

    $jobcarditem = new Jobcarditem();
    $employeeitem = new Productionitem();
    $employeeitem->productionId = $productionId;
    if ($myButton == "fetchData") {
        

        $fetchId = $request->get('fetchId');
        
        $jobcarditem = null;
        $jobcarditemInfo = $request->get('jobcarditemInfo');
        $jobcarditemInfo = trim($jobcarditemInfo);
        if ($fetchId == "jobcarditemId") {
           
           $jobcarditem = DB::table('jobcarditems')->where('id', $jobcarditemInfo)->first();
            
           
        } else if ($fetchId == "barcode") {
          
           $jobcarditem = DB::table('jobcarditems')->where('barcode', $jobcarditemInfo)->first();
        }

        
        if ($jobcarditem != null) {
         
           $product = DB::table('porducts')->where('id', $jobcarditem->productId)->first();
           $employeeitem = new Productionitem();
      
           $employeeitem->jobcarditemId = $jobcarditem->id;
          
           $employeeitem->productId = $jobcarditem->productId; 
          
           $employeeitem->unitId = $product->unitTypeId;
           
           $employeeitem->productionId = $productionId;

           $employeeitem->other = $request->other;

           $employeeitem->qnt = $request->qnt;

           $employeeitem->userId = Auth::id();

           $employeeitem->other = $request->other;

           $employeeitem->counttype = 0;


           $employeeitem->weight = 0;

           //$productionperemployee->state = 44;

           

           ///$productionSum =  DB::table('productionperemployees')->where('jobcarditemId',$productionperemployee->jobcarditemId)->sum('qnt');

        

           

           //$jobcarditemSum =  DB::table('jobcarditems')->where('id',$productionperemployee->jobcarditemId)->sum('qnt');
  
    
   

           //$productionperemployee->outstanding =  $jobcarditemSum -  $productionSum ;

       
           

 
        }
        
        View::share('employeeitem', $employeeitem);
        View::share('jobcarditem', $jobcarditem);

        return view('employeeitems.create',compact('employeeitem'), compact('jobcarditem'));

    } else if ($myButton == "create") {
         //Must save data and go back to list if production quantity is no greater than jobcarditem quantity
         
          $employeeitem = new Productionitem();
      
            $employeeitem ->jobcarditemId =  $request->jobcarditemId;
          
            $employeeitem ->productId = $request->productId; 
          
            $employeeitem ->unitId = $request->unitId;
           
            $employeeitem ->productionId = $productionId;

            $employeeitem ->other = $request->other;

            $employeeitem->userId = Auth::id();

            $employeeitem ->qnt = $request->qnt;

            $employeeitem ->other = $request->other;

            $employeeitem ->counttype = $request->counttype;

            $employeeitem ->weight = $request->weight;

            $employeeitem ->save();
         //save data and go to production.edit
         return redirect()->route('productionperemployees.edit', $employeeitem->productionId)->with('success','A new order iterm Has Been created successfully');
      
    }

    View::share('employeeitem', $employeeitem);
    View::share('jobcarditem', $jobcarditem);
  
    return view('employeeitems.create', compact('employeeitem'), compact('jobcarditem'));
       
      }
    
    
    
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
    
    $employeeitem = new Productionitem;
    $employeeitem->productionperemployeeId = $request->productionperemployeeId;
    $employeeitem->jobcarditemId = $request->jobcarditemId;
    $employeeitem->other = $request->other;
    $employeeitem->productId = $request->productId;
    $employeeitem->userId = Auth::id();
    $employeeitem->qnt = $request->qnt;
    $employeeitem->qntUnitId = $request->qntUnitId;
    //$employeeitem->state = 44;
    $employeeitem->save();
    
    
        $document = new DocumentAudit();
        $document->docId = $productionperemployee->id ;
        $document->docType = 'employeeitem started'; 
        $document->stateId  = 61;
        $document->other = 0;
        $document->userId = Auth::id();
        $document->action = 'Started';
        $document->save();
    
    return redirect()->route('productionperemployees.edit', $employeeitem->productionperemployeeId)->with('success','A new order iterm Has Been created successfully');
    }
    
    
    
    
    
    /**
    * Display the specified resource.
    *
    * @param  \App\Productionitem  $employeeitem
    * @return \Illuminate\Http\Response
    */
    public function show(Request $request,Productionitem $employeeitem)
    { 
    return view('employeeitems.show',compact('employeeitem'));
    //return redirect()->route('productionperemployees.edit',$employeeitem->productionperemployeeId);
    }
    
    
    
    
    
    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\Productionitem  $employeeitem
    * @return \Illuminate\Http\Response
    */
    public function edit(Productionitem $employeeitem)
    {
    return view('employeeitems.edit',compact('employeeitem'));
    }
    
    
    
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Productionitem  $Productionitem
    * @return \Illuminate\Http\Response
    */
    
    public function update(Request $request, $id)
    {
    
       $myButton= $request->get('myButton');
    
       if  ($myButton == "save"){
    
    $employeeitem = Productionitem::find($id);
    $employeeitem->jobcarditemId =  $request->jobcarditemId;      
    $employeeitem->productId = $request->productId; 
    $employeeitem->unitId = $request->unitId;
    $employeeitem->productionId = $request->productionId;
    $employeeitem->other = $request->other;
    $employeeitem->qnt = $request->qnt;
    $employeeitem->other = $request->other;
    $employeeitem->counttype = $request->counttype;
    $employeeitem->weight = $request->weight;
    //$employeeitem->state =44;
    $employeeitem->save();
    
    return redirect()->route('productionperemployees.edit',$employeeitem->productionId)
    ->with('success','A new order iterm Has Been updated successfully');
    
    } 
    
    
    }
    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Productionitem  $productionitem
    * @return \Illuminate\Http\Response
    */
    public function destroy(Productionitem $employeeitem)
    {
    $employeeitem->delete();
    return redirect()->route('productionperemployees.edit',$employeeitem->productionId)
    ->with('success','A new order iterm Has Been deleted  successfully');
    }
    
    
    public function getProductbyid(Request $request){
    
       $productid = $request->productid;
     
       $porduct = Porduct::select('*')->where('id', $productid)->get();
      
       // Fetch all records
       $response['data'] = $porduct;
     
       return response()->json($response);
     }
    
    
    }