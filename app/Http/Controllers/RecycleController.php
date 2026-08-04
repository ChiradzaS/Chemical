<?php

namespace App\Http\Controllers;

use App\Models\Recycle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecycleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


      $url = env('APP_URL1');

      $action =  $request->input('action');
      $shiftId =  $request->input('shiftId');
      $materialTypeId =  $request->input('materialTypeId');


    

      
   if ($action <> null && trim($action, ' ') == 'query') {

    $toDate =  $request->input('toDate');
    //$fromDate =  $request->input('fromDate');


    //dd($toDate);
   


    $fromDate = $request->get('fromDate');

    if ($fromDate == null ) {
       
       $fromDate = Carbon::today()->toDateString(); 
   }

 

    //$toDate = $request->get('toDate');
    if ($toDate == null) {
    $toDate = '2030-12-31';
    } 
    

    $shiftComp = '<>';
    if ($shiftId > 0) {
       $shiftComp = '=';
    }

    $materialTypeIdComp = '<>';
    if ($materialTypeId > 0) {
       $materialTypeIdComp = '=';
    }

 //d( $fromDate);
   

    $data = [

       'fromDate'            => $fromDate,
       'toDate'              => $toDate,
       'materialTypeId'      => $materialTypeId,
       'shiftId'             => $shiftId ,
       'shiftComp'           => $shiftComp ,
       'materialTypeIdComp'  => $materialTypeIdComp ,
       'date'  => $fromDate ,
       'search'              =>   10, 
  

    ];

    //dd($data);



    

    $response = Http::get($url.'/qryrecycle/index?data='.http_build_query($data));



    if ($response->successful()) {


      $data['recycles'] = json_decode($response);

      return  view('recycles.index',$data);
    
    } else {
        
        dd('Sorry , there an error with your request');
    
    }                                
   
 }



     


    
    
    
        $response = Http::get("$url/qryrecycle/index");
     
     
     
        if ($response->successful()) {
           
          $data['recycles'] = json_decode($response);
    
    

    
          return  view('recycles.index',$data);
    
          
        
        } else {
            
            dd('Sorry , there an error with your request');
        
        }
    
      
       
      }
     
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

      $shift = request('shift');      
      $material = request('material'); 
      $date = request('date');


      $date = Carbon::parse( $date )->format('Y-m-d');

      //dd($date);

   

      $data = Recycle::where('shiftId', $shift)
      ->whereDate('DateComplete', $date) // use whereDate to match only the date part
      ->where('materialTypeId', $material)
      ->get();

                      //dd($data);

      
      return view('recycles.create', ['recycles' => $data]);
   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        
$data = [
   
    'material'       => $request->materialTypeId,~
    'operator'       => $request->operator,
    'kilos'          => $request->kilos,
    'machineId'      => $request->machineId,
    'shiftId'        => $request->shiftId,

  ];



  Log::info('recycling data'.$data);

 



  $url = env('APP_URL1');
  
  
  $response = Http::get($url.'/qryrecycle/store',$data);
  
  
  if ($response->successful()) {
   
    $orderId = $response->json($response); 
    
    return redirect()->route('recycles.index')->with('success','Orders has been created successfully.');
  
  } else {
    
    dd('Sorry , there an error with your request . Please try again');
  
  }
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

      $object = Recycle::find($id);

      // Check if the object exists
      if ($object) {
          $object->delete();
          return redirect()->route('recycles.index');
      } else {
        return redirect()->route('recycles.index');
      }

      
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
    public function destroy(Request $request,$id)
    {
      dd('yooooooooh');
      return redirect()->route('recycles.index')
      ->with('success','Company has been deleted successfully');
      }
      
}
