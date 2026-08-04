<?php

namespace App\Http\Controllers;

use App\Models\Jobcarditem;
use App\Models\Productionitem;
use App\Models\Production;
use App\Models\Stock;
use App\Models\StocksTrans;
use Illuminate\Http\Request;
use App\Models\Porduct;
use App\Models\DocumentAudit;
use App\Models\Oder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;
use Carbon\Carbon;


class EmployeeproductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {      

        //dd('ooo');

        $value = $request->input('productType');

        $currentDate = Carbon::now()->toDateString();

        if($value){

            //dd('hoyo');

            $production = Production::where('userId', $value)
                                                ->whereDate('created_at', $currentDate)
                                                ->pluck('id');

            $data['productionitems'] = Productionitem::whereIn('productionId', $production)
                                                     ->where('stateId','<>' ,134)
                                                      ->orderBy('id','desc')->paginate(100);
                                                      //return view('productionitems.index', $data);

            //dd("here".$production );

               return view('productionitems.index', $data);
        }
        

        $data['productionitems'] = Productionitem::orderBy('id','desc')->paginate(50);
        return view('productionitems.index', $data);
      
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        
        // $value = $request->input('productType');
        // $currentDate = Carbon::now()->toDateString();
        
        // if($value){

        //     $production = Production::where('userId', $value)
        //                                         ->whereDate('created_at', $currentDate)
        //                                         ->pluck('id');

        //     $data['productionitems'] = Productionitem::whereIn('productionId', $production)
        //                                               ->orderBy('id','desc')->paginate(50);
        //     //return view('productionitems.index', $data);

        //     //dd("here".$production );

        //        //return view('productionitems.index', $data);
        //        return view('productionperemployees.create',$data,[ 'id'=> 0]);
        // }
      

        return view('productionperemployees.create',['productionitems' => 0, 'id'=> 0]);
            
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = $request->get('productId');
        $qnt = $request->get('qnt');
        $unit = $request->get('unit');
        $production = $request->get('productionId');
        $jobcard = $request->get('jobcard');

        $url = env('APP_URL1');



        if(!$jobcard ){
            $jobcard = 0;
        }

        if($production){

            // $productionitem = new Productionitem;
            // $productionitem->productionId = $production;
            // $productionitem->jobcarditemId = $jobcard ;
            // $productionitem->other = 'none';
            // $productionitem->productId = $product;
            // $productionitem->userId = Auth::id();
            // $productionitem->qnt = $qnt;
            // $productionitem->unitId = $unit;
            // $productionitem->save();

          

            $data = [

                'productionId' => $production,
                'jobcarditemId' => $jobcard,
                'other' => 'none',
                'productId' =>  $product,
                'userId' =>  Auth::id(),
                'qnt' => $qnt,
                'unitId' => $unit,
    
            ];

            $response = Http::get($url.'/qryproductionitems/store',$data);
  
  
            if ($response->successful()) {

                $dat = 10;

        
                return view('productionperemployees.create', ['id'=>$dat] );

          
            } else {
                
                dd('Sorry , there an error with your request');
            

        }

    }
//             if($jobcard > 0){

//               DB::table('jobcarditems')
//                    ->where('id', $jobcard )
//                    ->update(['stateId' => 62]); 

//  $jobcardId = DB::table('jobcarditems')
//                    ->where('id', $jobcard )
//                    ->value('jobCardId');

// $jobcardqnt = DB::table('jobcarditems')
//                    ->where('id', $jobcard )
//                    ->value('qnt');

//               DB::table('job_cards')
//                    ->where('id', $jobcardId  )
//                    ->update(['stateId' => 62]); 





//     $finaltOTALI = $jobcardqnt - $qnt;


//     DB::table('jobcarditems')
//        ->where('id', $jobcard )
//        ->update(['qnt' =>   $finaltOTALI]); 

//        DB::table('job_cards')
//           ->where('id', $jobcardId )
//           ->update(['qnt' =>  $finaltOTALI ]); 

//     $complete = DB::table('job_cards')
//                 ->where('id',$jobcardId )
//                 ->value('qnt'); 

//      $currentDate = Carbon::now();

//         if($complete <= 0){

//             DB::table('job_cards')
//                ->where('id', $jobcardId  )
//                ->update(['stateId' => 45,
//                          'DateComplete' => $currentDate
//                           ]); 

//             DB::table('jobcarditems')
//                ->where('jobCardId', $jobcardId )
//                 ->update(['stateId' => 45
               
//             ]); 

//         }
 




//     //dd('finaltotal'.$final_total);




                           



                



                
//             }




         

//             $stocks=DB::table('stocks')->where('productId',$productionitem->productId )->get();
         
//          foreach ($stocks as $stock){
//             $id = $stock->id;
//             $prv = $stock->qnt;


//             $Lowestunit=DB::table('porducts')->where('id',$productionitem->productId )->pluck('unitTypeId');

//             $pack = DB::table('types')->where('id',$unit )->pluck('value');
//             $packet = DB::table('types')->where('id',$Lowestunit )->pluck('value');

       



//           $packValue = $pack[0] * $qnt;
//           $packetValue = $packet[0];

//            $qntperpacket =  $packValue  / $packetValue ;

          

      
//                  $stocktrans = new StocksTrans();
//                  $stocktrans->stockId = $id;
//                  $stocktrans->userId = Auth::id();
//                  $stocktrans->docId= $productionitem->id;
//                  $stocktrans->docType= 105;
//                  $stocktrans->qnt = $qntperpacket;
//                  $stocktrans->save();
      
//                  Stock::where('id', $id)
//                         ->update(['qnt' =>$stocktrans->qnt + $prv ,
//                                   'prvqnt' =>$prv  ]);
      
               
//                                 }
            

//             $productionitems = DB::table('productionitems')->where('productionId', $production)
//                                                            //->where('stateId','<>' ,134)
//                                                            ->get();
 
           
//             return view('productionperemployees.create',['productionitems' => $productionitems, 'id'=> $production]);

//         }
//     }

//     /**
//      * Display the specified resource.
//      *
//      * @param  \App\Production  $productionperemployee
//      * @return \Illuminate\Http\Response
//      */
//     public function show(Production $productionperemployee)
//     {
//         $productionitems=DB::table('productionitems')->where('productionId', $productionperemployee->id)->get();

//         View::share('production', $productionperemployee);
//         View::share('productionitems',$productionitems);
//         Log::info("Update productions ------------------------------------------- : ".$productionperemployee->id); 
//         Log::info("Update productions ------------------------------------------- : ".$productionitems); 
//         foreach ($productionitems as $productionitem){
//            $jobCardItemId = $productionitem->jobcarditemId;
     
//            //$sql = "qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding";
//            $outstanding = DB::table('jobcarditems')
//            ->selectRaw("qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding")
//            ->where('id', $jobCardItemId)
//            ->value('outstanding'); 
     
//            $productionitem->outstanding = $outstanding;
//         }    
     
     

    }

    /**
     * Show the form for editing the specified resource.
     *
     *@param  \App\Production  $productionperemployee
     * @return \Illuminate\Http\Response
     */
    public function edit(Production $productionperemployee)
    {

        $employeeitems=DB::table('productionitems')->where('productionId', $productionperemployee->id)->get();

        View::share('productionperemployee', $productionperemployee);
        View::share('employeeitems',$employeeitems);
        
      
        foreach ($employeeitems as $employeeitem){
                   $jobCardItemId = $employeeitem->jobcarditemId;
     
                   
                   $outstanding = DB::table('jobcarditems')
                   ->selectRaw("qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding")
                   ->where('id', $jobCardItemId)
                   ->value('outstanding'); 
                        
     
                   $employeeitem->outstanding = $outstanding;
                   
     
        }  

        return view('productionperemployees.edit',compact('productionperemployee'));
        
        
    }

   /**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  \App\Production   $productionperemployee
* @return \Illuminate\Http\Response
*/
    public function update(Request $request, $id)
    {
        $myButton = $request->get('myButton');
   


   
 if ($myButton == "finish") {

    $productionperemployee = Production::find($id);
    $productionperemployee->stateId= 45;
    $productionperemployee->save();
   
   $document = new DocumentAudit();
   $document->docId =  $id;
   $document->docType = 'productionItem'; 
   $document->stateId = $productionperemployee->stateId;
   $document->other = $productionperemployee->other;
   $document->userId = Auth::id();
   $document->action = 'Complete';
   $document->save();
   
   DB::table('productionitems')
                ->where('productionId',  $productionperemployee->id)
                ->update(['stateId' => 45]);
   
   return redirect()->route('productionperemployees.index')
   ->with('success','Production sucessfully Completed');
   
   }

   else{
        
       
       $productionperemployee = Production::find($id);
       $productionperemployee->refNo = $request->refNo;
       $productionperemployee->other = $request->other;
       $productionperemployee->value = $request->value;
       $productionperemployee->processId = $request->processId;
       $productionperemployee->machineryId = $request->machineryId;
       $productionperemployee->employeeId = $request->employeeId;
       $productionperemployee->serialNo = $request->serialNo;
       $productionperemployee->shiftId = $request->shiftId;
       $productionperemployee->userId = Auth::id();
       $productionperemployee->stateId = 61;
       $productionperemployee->save();
       return redirect()->route('productionperemployees.edit',$id)
       ->with('success','Production Has Been updated successfully');
    }
    
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Production   $productionperemployee
    * @return \Illuminate\Http\Response
    */

    public function destroy(Production  $productionperemployee)
    {

    $productionperemployee->delete();
    return redirect()->route('productionperemployees.index')
    ->with('success','Production has been deleted successfully');
    }
}
