<?php

namespace App\Http\Controllers;


use App\Models\Jobcarditem;
use App\Models\Recipe;
use App\Models\Porduct;
use App\Models\Package;
use App\Models\DocumentAudit;
use App\Models\JobCard;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Illuminate\Support\Collection;

class JobcarditemController extends Controller
{
    
    
    public function index(Request $request)
    {
      $productId = $request->get('productId');
      $itemsList = $request->get('jocarditems');
      $stocklist = $request->get('jocardtype');
      $action = $request->get('action');
      $fromDate = $request->get('fromDate');
      $toDate   =   $request->get('toDate');
      $product  =  $request->get('productId');
      $customer =  $request->get('customerId');
      $stateId  =  $request->get('stateId');

      $jobcard   =   $request->get('jobcard');


      $fromDate = $request->get('fromDate');
      if ($fromDate == null) {
        $fromDate = '2020-12-31';
      }

      $toDate = $request->get('toDate');
      if ($toDate == null) {
        $toDate = '2030-12-31';
      }

      if ($jobcard) {

        $productComp = '<>';
        if ($product > 0) {
           $productComp = '=';
        }
  
        $stateComp  = '<>';
        if ($stateId  > 0) {
           $stateComp  = '=';
        }
  
        $customerComp  = '<>';
        if ($customer  > 0) {
           $customerComp = '=';
        }

        $jobcardId = DB::table('jobcarditems')
                        ->where('id', $jobcard )
                        ->value('jobCardId');

        if(!$jobcardId){

          Session::flash('message', 'Jobcard id not available please check validity');
          Session::flash('alert-class', 'alert-danger');
      
        
          return redirect()->back();


        }



        $data['job_cards'] = JobCard::where('id',$jobcardId)  
                                    ->orderBy('id', 'desc')
                                    ->get();

        return view('jobcarditems.jobcardstocklist', $data); 

      
      }




      if ($action <> null && trim($action, ' ') == 'query') {

  


        $productComp = '<>';
      if ($product > 0) {
         $productComp = '=';
      }

      $stateComp  = '<>';
      if ($stateId  > 0) {
         $stateComp  = '=';
      }

      $customerComp  = '<>';
      if ($customer  > 0) {
         $customerComp = '=';
      }

    //dd('dump//######'.$toDate);

    $data['job_cards'] = JobCard::whereDate('created_at', '>=', $fromDate)
                                ->whereDate('created_at', '<=', $toDate)
                                ->where('productId', $productComp, $product) 
                                ->where('stateId', $stateComp,$stateId) 
                                ->where('customerId', $customerComp ,$customer ) 
                                ->orderBy('id', 'desc')
                                ->get();

     
      
      


      //  dd('sta'.$stateId );


        return view('jobcarditems.jobcardstocklist', $data); 



      }


      if(  $stocklist != null ){

        $data['job_cards'] = JobCard::whereNull('jobcardType')
                                  ->orderBy('id','desc')
                                  ->where('stateId','<>', 45 )
                                  ->paginate(100);

    foreach ($data['job_cards'] as $dat){
      $jobCardId = $dat->id;
      $productId = $dat->productId;

    
                                 
      $jobCardItemId = DB::table('jobcarditems')
      ->where('jobCardId', $jobCardId)
      ->where('productId', $productId)
      ->value('id'); 
                                 
        

      return view('jobcarditems.jobcardstocklist', $data); 
 

        
       }



      }

      if($itemsList != null){

        
    $data['job_cards'] = JobCard::whereNull('jobcardType')
                                  ->orderBy('id','desc')
                                  ->paginate(1000);

    foreach ($data['job_cards'] as $dat){
      $jobCardId = $dat->id;
      $productId = $dat->productId;

    
                                 
      $jobCardItemId = DB::table('jobcarditems')
      ->where('jobCardId', $jobCardId)
      ->where('productId', $productId)
      ->value('id'); 
                                 
      // $outstanding = DB::table('jobcarditems')
      // ->selectRaw("qnt - (select sum(qnt) as qnt from `productionitems` where jobcardItemId = '".$jobCardItemId."') as outstanding")
      // ->where('id', $jobCardItemId)
      // ->value('outstanding'); 
      
    
      // $bEntry = false;
      // if ($outstanding >= 0) {
      //   $dat->outstanding = $outstanding;
      //   $bEntry = true;

      // }   
      // if($bEntry <> true) {
      //    $dat->outstanding = $dat->qnt;
      // }    

      return view('jobcarditems.jobcarditemslist', $data); 
 
 } 







        
        
      }



      

      $porduct =DB::table('porducts')->where('id', $productId )->first();
      View::share('porduct', $porduct);

      $recipeList=DB::table('recipes')->where('productId',  $productId)->get();
      

      $packageList=DB::table('packages')->where('productId',  $productId)->get();
      
            
      if(count($packageList)==0){

        $productList = DB::table('porducts')->where('id',  $productId)->get();
        View::share('productList',$productList);
        
      }
  
      
      $productList = DB::table('porducts')->where('id',  $productId)->get();
      View::share('productList',$productList);
      
  
     
     //echo "<pre>";
     //print_r( $porduct);
     //exit;
      $jobcarditem = Jobcarditem::find($request->id);
      Log::info(" - 1 Update Job Card ------------------------------------------- : ".$jobcarditem);

      View::share('recipes',$recipeList);
      View::share('packages',$packageList);
    
      View::share('jobcarditem', $jobcarditem);


      $List = jobcarditem::find($productId);
      Log::info(" - 1 Update Job Card ------------------------------------------- : ".$List);
     // $jobcard = DB::table('job_cards')->where('id',  $jobcarditem->jobCardId)->get();
      $jobcard = Jobcarditem::find( $jobcarditem->jobCardId);
       View::share('jobcard',$jobcard);


      return view('jobcarditems.index', ['productId' => $productId ]);

    }
    
    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create(Request $request)
    {  
      $button = $request->get('btrn');
      $jobCardId = $request->get('jobCardId'); 
      $docType = ('jobcarditem R&P');
     $docTypes = ('jobcard&items');

     $jobcarditemList=DB::table('jobcarditems')->where('jobCardId',$jobCardId)->pluck('id');
    
    //  echo "<pre>";
    //  print_r( $jobcarditemList);
    // exit;

   
      if ($button == 'audit')
      {

        $data['audits'] = DocumentAudit::wherein('docId', $jobcarditemList)
                                          //->where('docType', $docType)
                                          //->where('docType', $docTypes)
                                          //->where('docId',$jobCardId)
                                          ->paginate(50);  

                                                 
         return view('audits.index', $data);  
      }

      if ($button != 'audit'){
        $jobCardId = $request->get('jobCardId');      
 
        return view('jobcarditems.create', ['jobCardId' => $jobCardId]);
      }
      
    }
    
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {


      $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // Store the image
    $image = $request->file('image');
    $filename = Str::random(10) . '.' . $image->getClientOriginalExtension();
    $path = $image->storeAs('images', $filename, 'public');


    $product->image_path = $path;
    


    $jobcarditem = new Jobcarditem;
    $jobcarditem->name = $request->name;
    $jobcarditem->jobCardId = $request->jobCardId;
    $jobcarditem->productId= $request->productId;
    $jobcarditem->bagType= $request->bagType;
    $jobcarditem->barcode= $request->barcode;
    $jobcarditem->other = $request->other;
    $jobcarditem->stateId= 61;
    $jobcarditem->processId = $request->processId;
    $jobcarditem->qnt = $request->qnt;
    $jobcarditem->userId = Auth::id();
    $jobcarditem->unitId = $request->unitId;
    $jobcarditem->save();

 
  

    $document = new DocumentAudit();
    $document->docId =  $jobcarditem->id ;
    $document->docType = 'Jobcarditem started'; 
    $document->stateId  = 61;
    $document->other = 0;
    $document->userId = Auth::id();
    $document->action = 'Started';
    $document->save();

    return redirect()->route('job_cards.edit',$jobcarditem->jobCardId)
    ->with('success','Jobcarditem Has been successfully added.');
    }
    
    /**
    * Display the specified resource.
    *
    * @param  \App\jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function show(Jobcarditem $jobcarditem,Request $request)
    {
      

    return view('jobcarditems.show',compact('jobcarditem'));
    } 
    
    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\Jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function edit(Request $request, Jobcarditem $jobcarditem)
    {
      $types = DB::table('types')->get();


      $typesKeys = array();
    foreach ($types as $type) {
        $typesKeys[$type->id] = $type;
    }  

      $productId = $request->get('productId');
      Log::info(" Product Id Edit  ------------------------------------------- : ".$productId); 
    
      $porduct =DB::table('porducts')->where('id', $productId )->first();
      View::share('porduct', $porduct);

      $jobcard =DB::table('job_cards')->where('id', $jobcarditem->jobCardId )->first();
      //  echo "<pre>";
      //  print_r( $jobcard );
      //  exit;
      
      
    
      //$productId = $jobcarditem->productId;
      $jobcarditem->productId = $productId;
      $recipeList = null;
      $recipeList= DB::table('recipes')->where('productId',  $productId)->get();
      Log::info("list ------------------------------------------- : ".$recipeList); 
   
      //$packageList = null;
      $packageList = DB::table('packages')->where('productId',  $productId)->get();
           
      if(count($packageList)==0){

        $productList = DB::table('porducts')->where('id',  $productId)->get();
        View::share('productList',$productList);
        
      }
      
           
         View::share('packageList',$packageList);

    
     
      $productList = DB::table('porducts')->where('id',  $productId)->get();
        View::share('productList',$productList);

      // echo "<pre>";
      // print_r( $jobcarditem->processId );
      
      $tmpProcessType = $typesKeys[$jobcarditem->processId]; 
      $processId = ''.$tmpProcessType->name;

      // echo "<pre>";
      // print_r( $tmpProcessType->name );
      // exit;

      $unitType = $typesKeys[$jobcarditem->unitId]; 
      $val = ''.$unitType->name;

      if ($recipeList == null) {
        $recipeList = array( );
     }
      
      if ($packageList == null) {
        $packageList = array( );
      }

  
      
     // $jobcard = Jobcard::find( $jobcarditem->jobCardId);

      View::share('jobcard',$jobcard);
      View::share('jobcarditem',$jobcarditem);
      
      View::share('recipeList',$recipeList);

      
  
      Log::info(" Enter edit ------------------------------------------- : "); 
      return view('jobcarditems.edit',compact('jobcarditem'));
    }
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request,$id)
    {
      Log::info(" Enter update ------------------------------------------- : "); 
      $request->validate([
 
      ]);


      $productId = $request->get('productId');
      Log::info(" Product Id Edit  ------------------------------------------- : ".$productId); 
      

    $mySave= $request->get('mySave');  

    Log::info(" Save ------------------------------------------- : ".$mySave); 

    if ($mySave == "save") {
    
    $jobcarditem = Jobcarditem::find($id);
    $jobcarditem->name = $request->name;
    $jobcarditem->jobCardId = $request->jobCardId;
    $jobcarditem->productId= $request->productId;
    $jobcarditem->bagType= $request->bagType;
    $jobcarditem->barcode= $request->barcode;
    $jobcarditem->other= $request->other;
    $jobcarditem->stateId= 61;
    $jobcarditem->processId = $request->processId;
    $jobcarditem->qnt = $request->qnt;
    $jobcarditem->userId = 0;
    $jobcarditem->unitId = $request->unitId;

    $jobcarditem->save();

    return redirect()->route('job_cards.edit',$jobcarditem->jobCardId)
    ->with('success','Jobcarditem Has Been updated successfully');

  } else {

    Log::info(" Else list ------------------------------------------- : "); 
   
    $productId = $request->get('productId');
    Log::info(" Edit qqqqqqqqqqqqqqqqqqqqqq ------------------------------------------- : ".$productId); 
    $recipeList = null;
    $recipeList= DB::table('recipes')->where('productId',  $productId)->get();
    Log::info("list ------------------------------------------- : ".$recipeList); 
  
    $packageList = null;
    $packageList = DB::table('packages')->where('productId',  $productId)->get();
    Log::info("list ------------------------------------------- : ".$packageList); 
    
  
    $porduct =DB::table('porducts')->where('id', $productId )->first();
    View::share('porduct', $porduct);
  
    $jobcarditem = Jobcarditem::find($request->id);
    Log::info(" - 1 Update Job Card ------------------------------------------- : ".$jobcarditem);
  
   if ($recipeList == null) {
      $recipeList = array( );
   }
    
   if ($packageList == null) {
     $packageList = array( );
   }

   $jobcard = DB::table('job_cards')->where('id',  $jobcarditem->jobCardId)->get();
    

   View::share('jobcard',$jobcard);
    View::share('recipeList',$recipeList);
    View::share('packageList',$packageList);
  
    View::share('jobcarditem', $jobcarditem);
  
  
    $List = jobcarditem::find($productId);
    Log::info(" - 1 Update Job Card ------------------------------------------- : ".$List);

    return redirect()->route('jobcarditems.edit', ['jobcarditem' => $jobcarditem,'productId' => $productId])
    ->with('success','Jobcarditem Has Been updated successfully');

    //return redirect()->route('jobcarditems.edit',$jobcarditem->id)
    //->with('success','Jobcarditem Has Been updated successfully');
  }

    }

   /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function delete(Jobcarditem $jobcarditem)
    {
    $jobcarditem->delete();
    
    return redirect()->route('job_cards.edit',$jobcarditem->jobCardId)
    ->with('success','Jobcarditem has been deleted successfully');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function destroy(Jobcarditem $jobcarditem)
    {
       $jobcarditem->delete();
       return redirect()->route('job_cards.edit',$jobcarditem->jobCardId)
       ->with('success','Jobcarditem has been deleted successfully');
    }

    


public function actionview(Request $request) {

  $id =   $request->item;
 
 
  $url = env('APP_URL1');
 

 
 
 
  $service_url = $url.'/qryjobcarditems/show?id='.$id ;
  $curl = curl_init($service_url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $curl_response = curl_exec($curl);
 
  
 
  if ($curl_response == true ) {
 
    $jsonResponse = json_decode( $response , true);

    $id = $jsonResponse['product'] ?? null;
    $bagType = $jsonResponse['jobcarditems'] ?? null;
 
   
   
 
   $service_url = $url.'/qryjobcards/show?itemid='.$id ;
   $curl = curl_init($service_url);
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
   $curl_respons = curl_exec($curl);
 
   $data['orders'] = json_decode($curl_response, true);
 
   
  
 
   $data['orderitems'] = json_decode($curl_respons, true);
   
 
   
   
 
   // Assuming 'orderitems' is the key containing the array of order items
   $orderitems = $data['orderitems'];
   $orders = $data['orders'];
  
   // Pass the $orderitems variable to the view
   return view('orders.show')
     ->with('orderitems', $orderitems)
     ->with('orders', $orders);
 
 
 
   
   //View::share('orderitems', $orderitems);
   // View::share('orders', $orderData  );
 
  
   // return view('orders.show', ['orderitems' => $orderitems]);
  
  } else {
      
      dd('Sorry , there an error with your request');
  
  }
 
 
 }
 
 
 
 
 
 
 
 
 
 
 public function actionupdate(Request $request) {
 
   $id =  $request->item;

  // dd($id);

   
   $url = env('APP_URL1');
 
 
 
 
   
   $response = Http::get($url.'/qryjobcarditems/show?id='.$id);

 
    
    
   if ($response->successful()) {
  
    $jsonResponse = json_decode( $response, true);
   

  
    $product = $jsonResponse['product'] ?? null;

    $unitTypeId= $jsonResponse['product'][0]['unitTypeId'];
    $unitPackId= $jsonResponse['product'][0]['unitPackId'];
    $minW= $jsonResponse['product'][0]['minWeight'];
    $avgW= $jsonResponse['product'][0]['avgWorkingWeight'];
    $maxW= $jsonResponse['product'][0]['maxWeight'];
    $minpp= $jsonResponse['product'][0]['minWeightPerProduct'];
    $avgpp= $jsonResponse['product'][0]['avgWeightPerProduct'];
    

 
  
    $jobcarditem = $jsonResponse['jobcarditems'] ?? null;


   
   
     return view('jobcarditems.edit',['porduct' => $product,  'jobcarditem' =>  $jobcarditem , 'unit' => $unitTypeId,'pack' => $unitPackId,'minW' =>  $minW, 'avgW' =>  $avgW, 'maxW' =>  $maxW ,  'minpp' =>   $minpp, 'avgpp' =>   $avgpp, 'maxpp' =>   $avgpp ]);
 
   } else {
       
       dd('Sorry , there an error with your request');
   
   }
 
 }
 
 
 
 
 
 
 public function actiondelete(Request $request) {
 
   $id =  $request->item;
   
   $url = env('APP_URL1');
 

 
 
   $service_url = $url.'/qryjobcarditems/destroy?id='.$id;
   $curl = curl_init($service_url);
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
   $curl_response = curl_exec($curl);
 
   
   if ($curl_response === '1') {
      
 

 
 
     return redirect()->route('job_cards.index')
     ->with('success','Order successfully been deleted');
 
 
   
   } else {
       
       dd('Sorry , there an error with your request');
   
   }
 
 
 }

    
}