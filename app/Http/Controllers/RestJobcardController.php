<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use App\Models\Orders;
use App\Models\JobCard;
use App\Models\Porduct;
use App\Models\OrdersInProgress;
use App\Models\Jobcarditem;
use App\Models\Production;
use App\Models\Productionitem;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Carbon\Carbon;
use Auth;

class RestJobcardController extends Controller
{


    public function index(Request $request)
    {
        $search = $request->input('search');
        $allocation = $request->input('allocation');
        $reactlist = $request->query('data');

        $currentYear = Carbon::now()->year;



                if($reactlist){



    
        
        $threeMonthsAgo = Carbon::now()->subMonths(2);



        $response = Jobcard::join('jobcarditems', 'job_cards.id', '=', 'jobcarditems.jobCardId')
                            ->select(
                                    'job_cards.id as job_card_id',
                                    'job_cards.customerId as job_cards_customerId', 
                                    'job_cards.productId as job_cards_productId',                         
                                    'job_cards.created_at as job_cards_created_at',
                                    'job_cards.updated_at as job_cards_updated_at',
                                    'job_cards.stateId as job_cards_stateId',
                                    'job_cards.jobcardType as job_cards_jobcardType',
                                    'job_cards.outstanding as job_cards_outstanding',
                                    'jobcarditems.id as jobcarditem_id',                            
                                    'jobcarditems.productId as jobcarditem_productId',
                                    'jobcarditems.qnt as jobcarditem_qnt',
                                    'jobcarditems.unitId as jobcarditem_unitId',
                                    'jobcarditems.processId as jobcarditem_processId',
                                    'jobcarditems.outstanding as jobcarditem_outstanding',
                                    'jobcarditems.stateId as jobcarditem_stateId',
                                    'jobcarditems.other as jobcarditem_other'
                                )
                                ->groupBy(
                                    'job_cards.id', 
                                    'job_cards.customerId',   
                                    'job_cards.productId',                   
                                    'job_cards.created_at', 
                                    'job_cards.stateId',
                                    'job_cards.updated_at',
                                    'job_cards.outstanding', 
                                    'job_cards.jobcardType',
                                    'jobcarditems.id', 
                                    'jobcarditems.productId', 
                                    'jobcarditems.qnt', 
                                    'jobcarditems.unitId',
                                    'jobcarditems.processId',
                                    'jobcarditems.outstanding',
                                    'jobcarditems.stateId',
                                    'jobcarditems.other'
                                )

                              
                                ->where('job_cards.created_at', '>=', $threeMonthsAgo)
                                ->whereNull('job_cards.jobcardType')
                                ->where('job_cards.stateId','<>', 45)
                                 //->take(100) // or ->limit(30)
                                ->orderBy('job_cards.created_at', 'desc')
                               // ->orderBy('jobcarditems.processId', 'asc')
                                ->get();

        //Log::info($response);
        
 
        return response()->json($response);     
            
        }

        if( $allocation ){



    
            $data = Jobcard::join('jobcarditems', 'job_cards.id', '=', 'jobcarditems.jobCardId')
                            ->select(
                                'job_cards.id as job_card_id',
                                'job_cards.customerId as job_cards_customerId',                      
                                'job_cards.outstanding as job_cards_outstanding',
                                'jobcarditems.id as jobcarditem_id',                            
                                'jobcarditems.productId as jobcarditem_productId',
                                'jobcarditems.qnt as jobcarditem_qnt',
                                'jobcarditems.processId as jobcarditem_processId',
                                'jobcarditems.outstanding as jobcarditem_outstanding',
                                'jobcarditems.stateId as jobcarditem_stateId',
                                
                            )
                            ->groupBy(
                                'job_cards.id', 
                                'job_cards.customerId',                      
                                'job_cards.outstanding', 
                                'jobcarditems.id', 
                                'jobcarditems.productId', 
                                'jobcarditems.qnt', 
                                'jobcarditems.processId',
                                'jobcarditems.outstanding',
                                'jobcarditems.stateId',
                              
                            )
                        
                                ->where('jobcarditems.processId', 23)  
                                ->where('job_cards.stateId','<>', 45)
                                ->orderBy('job_cards.updated_at', 'desc')
                                ->take(20) // or ->limit(30)
                                ->get();
                            


        
        
            return response()->json($data);
        }

        if( $search){

            $customerId = $request->input('customerId');
   
            $productId = $request->input('productId');

           
        
            $toDate = $request->input('toDate');
         
            $fromDate = $request->input('fromDate');
        
            $startDate= $request->input('startDate');

            $productComp = $request->input('productComp');

       

            $customerComp = $request->input('customerComp');



    
            $data = Jobcard::join('jobcarditems', 'job_cards.id', '=', 'jobcarditems.jobCardId')
                            ->select(
                                'job_cards.id as job_card_id',
                                'job_cards.customerId as job_cards_customerId',                       
                                'job_cards.created_at as job_cards_created_at',
                                'job_cards.updated_at as job_cards_updated_at',
                                'job_cards.stateId as job_cards_stateId',
                                'job_cards.jobcardType as job_cards_jobcardType',
                                'job_cards.outstanding as job_cards_outstanding',
                                'jobcarditems.id as jobcarditem_id',                            
                                'jobcarditems.productId as jobcarditem_productId',
                                'jobcarditems.qnt as jobcarditem_qnt',
                                'jobcarditems.unitId as jobcarditem_unitId',
                                'jobcarditems.processId as jobcarditem_processId',
                                'jobcarditems.outstanding as jobcarditem_outstanding',
                                'jobcarditems.stateId as jobcarditem_stateId',
                                'jobcarditems.other as jobcarditem_other'
                            )
                            ->groupBy(
                                'job_cards.id', 
                                'job_cards.customerId',                      
                                'job_cards.created_at', 
                                'job_cards.stateId',
                                'job_cards.updated_at',
                                'job_cards.outstanding', 
                                'job_cards.jobcardType',
                                'jobcarditems.id', 
                                'jobcarditems.productId', 
                                'jobcarditems.qnt', 
                                'jobcarditems.unitId',
                                'jobcarditems.processId',
                                'jobcarditems.outstanding',
                                'jobcarditems.stateId',
                                'jobcarditems.other'
                            )
                                ->whereDate('job_cards.created_at', '<=', $toDate)
                                ->whereDate('job_cards.created_at', '>=', $fromDate)   
                                ->where('job_cards.customerId', $customerComp, $customerId)  
                                ->where('job_cards.productId', $productComp, $productId)  
                                ->orderBy('job_cards.updated_at', 'desc')
                                ->get();
                            


        
        
            return response()->json($data);
        }

        
        $threeMonthsAgo = Carbon::now()->subMonths(1);



        $response = Jobcard::join('jobcarditems', 'job_cards.id', '=', 'jobcarditems.jobCardId')
                            ->select(
                                    'job_cards.id as job_card_id',
                                    'job_cards.customerId as job_cards_customerId',                       
                                    'job_cards.created_at as job_cards_created_at',
                                    'job_cards.updated_at as job_cards_updated_at',
                                    'job_cards.stateId as job_cards_stateId',
                                    'job_cards.jobcardType as job_cards_jobcardType',
                                    'job_cards.outstanding as job_cards_outstanding',
                                    'jobcarditems.id as jobcarditem_id',                            
                                    'jobcarditems.productId as jobcarditem_productId',
                                    'jobcarditems.qnt as jobcarditem_qnt',
                                    'jobcarditems.unitId as jobcarditem_unitId',
                                    'jobcarditems.processId as jobcarditem_processId',
                                    'jobcarditems.outstanding as jobcarditem_outstanding',
                                    'jobcarditems.stateId as jobcarditem_stateId',
                                    'jobcarditems.other as jobcarditem_other'
                                )
                                ->groupBy(
                                    'job_cards.id', 
                                    'job_cards.customerId',                      
                                    'job_cards.created_at', 
                                    'job_cards.stateId',
                                    'job_cards.updated_at',
                                    'job_cards.outstanding', 
                                    'job_cards.jobcardType',
                                    'jobcarditems.id', 
                                    'jobcarditems.productId', 
                                    'jobcarditems.qnt', 
                                    'jobcarditems.unitId',
                                    'jobcarditems.processId',
                                    'jobcarditems.outstanding',
                                    'jobcarditems.stateId',
                                    'jobcarditems.other'
                                )

                              
                                ->where('job_cards.created_at', '>=', $threeMonthsAgo)
                                ->whereNull('job_cards.jobcardType')
                                ->where('job_cards.stateId','<>', 45)
                                 //->take(100) // or ->limit(30)
                                ->orderBy('job_cards.created_at', 'desc')
                                ->orderBy('jobcarditems.processId', 'desc')
                                ->get();

        //Log::info($response);
        
 
        return response()->json($response);   
    }




    public function store(Request $request)
    {




         $items = $request->input('item');
        $dataString = $request->query('data');

              $allocationData = json_decode(urldecode($dataString), true);
               
                $jobCardData = $allocationData['jobCard'] ?? null;
                $reactitems       = $allocationData['items'] ?? [];

                if ($jobCardData) {
                    // === Create JobCard ===
                    $job_card = new JobCard;
                    $job_card->qnt                = $jobCardData['qnt'] ?? null;
                    $job_card->outstanding        = $jobCardData['qnt'] ?? null;
                    $job_card->productId          = $jobCardData['productId'] ?? null;
                    $job_card->unitId             = $jobCardData['unitId'] ?? null;
                    $job_card->barcode            = $jobCardData['barcode'] ?? null;
                    $job_card->bagType            = $jobCardData['bagType'] ?? null;
                    $job_card->customerId         = $jobCardData['customerId'] ?? null;
                    $job_card->image_path         = $jobCardData['image_path'] ?? null;
                    $job_card->userId             = $jobCardData['userId'] ?? null;
                    $job_card->stateId            = 61;
                    $job_card->startDate          = $request->query('other') ?? now()->toDateString();
                    $job_card->save();

                    // === Create JobCard Items ===
                    foreach ($reactitems as $item) {


                        $jobcarditem = new Jobcarditem;
                        $jobcarditem->jobCardId   = $job_card->id;#



                        $processName   = $item['processId'] ?? null;

                        if (!empty($processName)) {
                        $processTypeId = Type::where('name', $processName)->where('groupType','processes')->value('id');

                        if ($processTypeId) {
                            // $processTypeId now holds the ID you need for your jobcarditem
                            $jobcarditem->processId = $processTypeId;
                        }
                        }


                        $jobcarditem->productId   = $item['productId'] ?? null;
                        $jobcarditem->qnt         = $item['qnt'] ?? null;
                        $jobcarditem->unitId      = $item['unitId'] ?? null;
                        $jobcarditem->bagType     = $item['bagType'] ?? null;
                        $jobcarditem->barcode     = $job_card->barcode ;
                        $jobcarditem->name     = $job_card->barcode ;
                        $jobcarditem->outstanding = $item['qnt'] ?? null;

                            // 🔹 Get bagType from the Product model
                        if (!empty($item['productId'])) {
                            $product = Porduct::find($item['productId']);
                            if ($product) {
                                $jobcarditem->bagType = $product->bagType; // assumes column is bagType
                            }
                        }
                                            $jobcarditem->save();
                    }

                    return response()->json([
                        'status' => 'JobCard + items created',
                        'jobCardId' => $job_card->id,
                        'itemsCount' => count($reactitems),
                    ]);
                }

       

        

        if(!$items){

            $job_card = new JobCard;
            $job_card->refNo = $request->filled('refNo') ? $request->input('refNo') : null;
            $job_card ->description  = $request->input('description')??'';
            $job_card ->qnt  = $request->input('qnt');
            $job_card ->outstanding  = $request->input('qnt');
            $job_card ->startDate  = $request->input('other') ??now()->toDateString();
            $job_card ->productId  = $request->input('productId');
            $job_card ->unitId  = $request->input('unitId');
            $job_card ->barcode  = $request->input('barcode');
            $job_card ->noOfProcesses =$request->input('noOfProcesses');
            $job_card ->weightper1000   = $request->input('weightper1000 ');
            $job_card ->bagType  = $request->input('bagType');
            $job_card ->customerId  = $request->input('customerId');
            $job_card ->orderId  = $request->input('order');
            $job_card ->other = $request->input('other');
            $job_card ->stateId  = $request->input('stateId');
            $job_card ->userId =  $request->input('userId');
            $job_card ->image_path =  $request->input('image_path');
            $job_card ->save();


            $orderId  = $request->input('order');

            if($orderId){

                $url = env('APP_URL');




                $response = Http::get($url.'/qryorderitems/changestate?id='.$orderId);



                if (!$response->successful()) {
                       
                    $order = new OrdersInProgress;
                    $order->orderId = $orderId;
                    $order->save();
 
                   }


            }






            
            $response = [
                'id' => $job_card->id,
                'bagType' => $job_card->bagType,
                'orderId' =>  $orderId ,
  
            ];

            return response()->json($response);


        }else if($items){

            $jobcarditem = new Jobcarditem;
            $jobcarditem->jobCardId = $request->input('jobCardId');
            $jobcarditem->bagType = $request->input('bagType');
            $jobcarditem->processId = $request->input('processId');
            $jobcarditem->productId = $request->input('productId');
            $jobcarditem->qnt = $request->input('qnt');
            $jobcarditem->outstanding = $request->input('qnt');
            $jobcarditem->unitId = $request->input('unitId');
            $jobcarditem->barcode = $request->input('barcode');
            $jobcarditem->other= $request->input('other');
            $jobcarditem->userId = $request->input('userId');
            $jobcarditem->orderId = $request->input('orderId');
            $jobcarditem->name = $request->input('name');
            $jobcarditem->save();



            $response = 1;



            return response()->json($response);

            
        }



  
        


       // $noProcesses =  $job_card ->noOfProcesses;

 


   
        // for ($i = 1; $i <= $noProcesses; $i++) {
          
        //    $jobcarditem = new Jobcarditem;
        //    $jobcarditem->jobCardId = $job_card->id;
        //    $jobcarditem->bagType = $job_card->bagType;
        //    $process = 'processid_'.$i;
        //    $valProcessId = ""; 
        //    $valProcessId = $request->input($process);
        //    if ($valProcessId != "") {
        //    $jobcarditem->processId = $valProcessId;
        //    $productId = 'productId_'.$i;
        //    $valProdId = $request->input($productId);
        //    $jobcarditem->productId = $valProdId; 
        //    $qnt = 'qnt_'.$i;
        //    $valQnt = $request->input($qnt);
        //    $jobcarditem->qnt = $valQnt; 
        //    $jobcarditem->outstanding = $valQnt;
        //    $unitId = 'unitId_'.$i;
        //    $valUnitId = $request->input($unitId);
        //    $jobcarditem->unitId = $valUnitId;  
        //    $jobcarditem->barcode = Barcode::uniqidReal();
        //    $jobcarditem->other= $job_card->other;
        //    $jobcarditem->userId = Auth::id();
        //    $jobcarditem->name = $job_card->name.$jobcarditem->processId.$jobcarditem->barcode;
        //    $jobcarditem->save();
          
        //    }
        // }

        //$response = 1;



       //return response()->json($response);

    }




    public function show(Request $request)
    {


        $id = $request->input('id');


        $jobcard = Jobcard::select('*')->where('id', $id)->get();

        $productId = Jobcard::select('*')->where('id', $id)->value('productId');

       
        
     
       $product  =  Porduct::select('*')->where('id', $productId )->get();

       $jobcarditems = Jobcarditem::select('*')->where('jobCardId', $id)->get();

       $response = [

        'product' => $product,
        'jobcarditems' => $jobcarditems,
        'jobcard' => $jobcard,

       ];
        
        return response()->json($response);
    }




    public function update(Request $request)
    {
        $myButton= $request->get('myButton');

        if ($myButton == "clone") {
          $job_card = JobCard::find($id);
          $id = $request->id;
          $refNo = $request->refNo;
          $description = $request->description;
          $start = $request->startDate;
          $product= $request->product;
          $productId = $request->productId;
          $unit = $request->unitId;
          $process = $request->noOfProcesses;
          $qntUnitId = $request->qntUnitId;
          $qnt = $request->qnt;
          $bagType = $request->bagType;
          $qntType = $request->qntType;
          $qntUnit = $request->qntUnit;
          $orderId = $request->orderId;
          $customer = $request->customerId;
          $stateId = 61;
      
      
          
      
      
          $job_card = new JobCard;
          $job_card->refNo = Barcode::uniqidReal();
          $job_card->description = $description;
          $job_card->startDate = now();
          $job_card->barcode = Barcode::uniqidReal();
          $job_card->productId = $productId;
          $job_card->unitId = $unit;
          $job_card->noOfProcesses = $process;
          $job_card->qnt = $qnt;
          $job_card->bagType = $bagType;
          $job_card->orderId = $orderId;
          $job_card->customerId =  $customer;
          $job_card->other = "none";
          $job_card->stateId = 61;
          $job_card->userId = Auth::id();
          $job_card->save();
      
      
          $jobcarditemList = DB::table('jobcarditems')->where('jobCardId',$id)->get();
          
          
      
          foreach( $jobcarditemList as $item)
          {   
            $jobcarditem = new Jobcarditem;
            $jobcarditem->jobCardId =  $job_card->id;
            $jobcarditem->bagType = $item->bagType;
            $jobcarditem->processId = $item->processId;
            $jobcarditem->productId = $item->productId; 
            $jobcarditem->qnt = $item->qnt; 
            $jobcarditem->unitId = $item->unitId; 
            $jobcarditem->barcode = Barcode::uniqidReal();
            $jobcarditem->other= $item->other;
            $jobcarditem->stateId= 61;
            $jobcarditem->userId = Auth::id();
            $jobcarditem->name = $item->name;
            $jobcarditem->save();
      
      
            return redirect()->route('job_cards.index')
            ->with('success','You have  successfully added a new Cloned jobcard');
      
          }
      
        
      
        }
      
      
      $request->validate([
        'name' => 'required',
        ]);
        $job_card = JobCard::find($id);
        $job_card->name = $request->name;
        $job_card->refNo = $request->refNo;
        $job_card->description = $request->description;
        $job_card->startDate = $request->startDate;
        $job_card->product = $request->product;
        $job_card->productId = $request->productId;
        $job_card->unit = $request->unit;
        $job_card->noOfProcesses = $request->noOfProcesses;
        $job_card->qntUnitId= $request->qntUnitId;
        $job_card->qnt = $request->qnt;
        $job_card->bagType = $request->bagType;
        $job_card->weightper1000 = $weightper1000;
        $job_card->qntType = $request->qntType;
        $job_card->qntUnit = $request->qntUnit;
        $job_card->customer = $request->customer;
        $job_card->stateId =61;
        $job_card->userId = Auth::id();
        $job_card->save();
        
        return redirect()->route('job_cards.index')
        ->with('success','Product Has Been updated successfully');
                
        return response()->json( $response);
    }




    public function destroy(Request $request)
    {


        $data = $request->input('id');


        $result = DB::table('job_cards')
        ->where('id', $data)
        ->delete();

        $response = 1;

       

                
            return response()->json( $response);



    

    }

    public function productionj(Request $request)
    {


        $jobCardId = $request->input('id');


            
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
            'productionitems.unitId as item_unitId',
            'productionitems.jobcarditemId as item_jobcarditemId',
            'productionitems.stateId as item_stateId'
        )
      
        ->where('productionitems.stateId','<>',134)
        ->where('productionitems.jobcarditemId',$jobCardId)
        ->orderBy('productions.machineryId', 'desc')
        ->orderBy( 'productions.shiftId', 'asc')
        ->orderBy( 'productions.created_at', 'asc')
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

        $machines[$record->production_machineryId]['productions'][$record->production_id]['items'][] = [
            'item_id' => $record->item_id,
            'productId' => $record->item_productId,
            'quantity' => $record->item_qnt,
            'unitId' => $record->item_unitId,
            'jobcarditemId' => $record->item_jobcarditemId
        ];
    }

   
    $response = $machines;

       

                
            return response()->json($response);



    

    }



    public function reactdelete(Request $request)
{

    $jobCardId = $request->query('data');


    Log::info($jobCardId);


    try {
        DB::beginTransaction();
        
        DB::table('jobcarditems')->where('jobCardId', $jobCardId)->delete();
        $deleted = DB::table('job_cards')->where('id', $jobCardId)->delete();
        
        if ($deleted === 0) {
            DB::rollBack();
            return response()->json(['success' => false], 404);
        }
        
        DB::commit();
        return response()->json(['success' => true]);
        
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false], 500);
    }
}
}
