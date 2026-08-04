<?php

namespace App\Http\Controllers;

use App\Models\Pickingslip;
use Illuminate\Support\Facades\View;
use App\Models\Order_item;
use App\Models\Delivery;
use App\Models\Invoices;
use App\Models\Invoice_item;
use Illuminate\Support\Facades\Log;
use App\Models\Orders;
use App\Models\Porduct;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use DB;

class PickingslipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $itemsList = $request->get('complete');

        if( $itemsList){

           

            $data['pickingslips'] = Pickingslip::select('customerId')
                                                ->distinct()
                                                ->orderBy('updated_at', 'desc') // Sorting by 'updated_at' in descending order
                                                ->paginate(50);


            return view('pickingslip.complete', $data);


        }

        $data['pickingslips'] = Pickingslip::select('customerId')
                                            ->distinct()
                                            ->where('stateId','<>',45)
                                            ->orderBy('updated_at', 'desc') // Sorting by 'updated_at' in descending order
                                            ->paginate(50);


        return view('pickingslip.index', $data);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pickingslip.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd('hokoyoooooooo');

        $orderitemId = Order_item::select('id')
                                    ->where('customerId', $request->customerId)
                                    ->where('productId', $request->productId)
                                    ->where('stateId', '<>', 45)
                                    ->first(); 


    if ($orderitemId === null) {

        $today = date('Y-m-d');
       
        $order = new Orders;
        //$order->reference = $request->reference;
        $order->date = $today;
        $order->other = 'null';
        $order->customerId = $request->customerId;
        $order->totalValue = $request->totalValue;
        $order->datePlaced = $today;
        $order->dueDate= $today;
        $order->stateId = 45;
        $order->userId = Auth::id();
        $order->save();

        $orderite = new Order_item;
        $orderite->ordersId = $order->id;
        $orderite->customerId = $order->customerId;
        $orderite->quantity = $request->qnt;
        $orderite->other = 'null';
        $orderite->unitId = $request->unitId;
        $orderite->price = 0.00;
        $orderite->openningQNT = $orderite->quantity;
        $orderite->dueDate = $today;
        $orderite->reference = 'null';
        $orderite->productId = $request->productId;
        $orderite->stateId = 45;
        $orderite->totalPrice = 0.00;
        $orderite->userId = Auth::id();
        $orderite->save();

        $orderitemId = $orderite->id;

    } else {
        $orderitemId = $orderitemId->id; 
    }

        

            $pickingslip = new Pickingslip;
            $pickingslip->driver = $request->driver;
            $pickingslip->unitId = $request->unitId;
            $pickingslip->customerId = $request->customerId;
            $pickingslip->qnt = $request->qnt;
            $pickingslip->productId = $request->productId;
            $pickingslip->stateId = 61;  
            $pickingslip->orderitemId =  $orderitemId;     
            $pickingslip->save();

            return view('pickingslip.create');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pickingslip  $pickingslip
     * @return \Illuminate\Http\Response
     */
    public function show( Request $request,$id)
    {

        

        $selectedValues = $request->input('selectedValues');
        $Id = $id;

        $orderId = $request->get('order');

        $selectedValues = $request->input('data');
        $dataArray = json_decode(urldecode($selectedValues), true);

        $orderitemId = DB::table('pickingslips')->where('id',$Id)->pluck('orderitemId');


        $hasString = strpos($id, 'yourString') !== false;

        if ($hasString) {

            $id = str_replace('yourString', '', $id);

            

        

            $orderitemIds  = DB::table('pickingslips')->where('stateId','<>',45)->where('customerId',$id)->pluck('orderitemId');

             

             $item  = DB::table('pickingslips')->where('customerId',$id)->value('orderitemId');
             
             $orderId = DB::table('order_items')->where('id',$item )->pluck('ordersId');
             
            
            
           

          

            $orderinfo  = DB::table('orders')->where('id',$orderId)->get();

            

            $orderitems = DB::table('order_items')->whereIn('id',$orderitemIds)->get();

            


            foreach($orderitems  as $orderitem){

                $qnt = $orderitem->quantity;

            }

         


            $qnt = DB::table('pickingslips')->where('id', $id)->value('qnt');
           
            
            $unit = DB::table('pickingslips')->where('id', $id)->value('unitId');
           // dd($unit );
           
            View::share('orderitems',$orderitems);
            View::share('orderinfo',$orderinfo );
           ///@ View::share('qnt',$qnt );
            //View::share('unit',$unit );
        

            return view('pickingslip.show');
            
           
           
    
        }else{
            $Id = DB::table('order_items')->where('id',$orderitemId)->pluck('ordersId');
            
         

            $orderinfo  = DB::table('orders')->where('id',$Id)->get();


            $orderitems = DB::table('order_items')->where('id', $orderitemId )->get();

            $qnt = DB::table('pickingslips')->where('id', $id)->value('qnt');
            $unit = DB::table('pickingslips')->where('id', $id)->value('unitId');

       

            View::share('orderitems',$orderitems);
            View::share('orderinfo',$orderinfo );
            View::share('qnt',$qnt );
            View::share('unit',  $unit );
            

            return view('pickingslip.show');
        }
        


    

   
      
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pickingslip  $pickingslip
     * @return \Illuminate\Http\Response
     */
    public function edit(Pickingslip $pickingslip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pickingslip  $pickingslip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pickingslip $pickingslip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pickingslip  $pickingslip
     * @return \Illuminate\Http\Response
     */
    public function destroy( Request $request , Pickingslip $pickingslip)
    {

        if ($request->input('delete_action') == 'true') {

            $pickingslip->delete();
            return redirect()->route('pickingslip.index')->with('success', 'Pickingslip has been deleted successfully');
           //dd('DUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUMP');
        }
        
        $pickingslip->delete();
        return redirect()->route('pickingslip.create')->with('success', 'Pickingslip has been deleted successfully');
    }

    public function getorderitem(Request $request)
    {

      
     
     $customer = $request->customer;


     $productIds= Order_item::select('*')->where('customerId',$customer)
                                         ->where('stateId','<>',45)
                                         -> pluck('productId');


        //$productIds = OrderItem::pluck('product_id');
    $products = Porduct::whereIn('id', $productIds)->get();

    if(!$products ){

        $response['data']  = 1;


    } else{

        $response['data'] = $products;

    }


  
   
         
     
    
   
     
   
    return response()->json($response);

    }

    public function changestatepickingslip(Request $request)
    {

        //$slips = $request->pickigslips ;
        $IdsArray = explode(',', $request->pickigslips);

        // // Log the exploded array for debugging
        // Log::info(" ----OOOOOOOOOOOOO777OOOOOOOOOOOOOOOOOOOOO-----OOOOOOOO: " . print_r($IdsArray, true));
        
        // Update records based on the array of IDs
        $productIds = Pickingslip::whereIn('id', $IdsArray)
                                 ->update(['stateId' => 45]);
        
        // Set the response
        $response = 0;
        
        // Return the JSON response
        return response()->json($response);

        
    }



    public function deliverslip(Request $request)
 {
    $picngId = $request->pic;
    $id = $request->productid;
    $customer = $request->customer;
    $qnt = 00;
    $ref = $request->reference;
    $product = $request->product;
    $invoice = $request->invoice;
    $unit = $request->unit;
    $total = $request->total;
    $prize = $request->prize;
    $discount = $request->discount;
    $pric = $request->price;
    $commaPosition = strpos($pric,',');
    $invoic = substr($pric, $commaPosition + 1);
    $invoiceId = trim($invoic);
    $createdIds = [];

    
   

    
        
        //dd('DUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUMP');


 

    // $commaPosition = strpos($pric,',');
    // $invoice = substr($pric, $commaPosition - 1);
    // $price = trim($invoice);

    $trimmedString = explode(',',$pric)[0];
    $price = trim($trimmedString);

    $uniqueNumber = $request->uniqueNumber;
    $vat = $request->vat;
    $vatperitem = $request->vatperitem;
    // $vatttotal = $request->vatt;
     $totalwthotvat = $request->totalwithVat;
    // $vatoutvat = $request->totalwithoutVat;
     ///$ttdscnt = $request->discountTotal;
    //$discountperitem = $request->discountper;
    //$invoiceId = $request->invoiceId;  
   // $invoiceId  = $request->input('responseData');
    
    // $variable = $request->qnt;

 
    

    
   
    
    $invoiceItems = [
        [
         "product" =>  $product,
         "quantity" => $request->quantity,
         "unit" => $unit,
         "ref" => $ref,
         "price" => $price,
         "prize" => $prize,
         "vatperitem" => $vatperitem, 
         "totalwithvat" => $totalwthotvat, 
         "vat" => $vat, 
         "total" => $total ,
         "discount" => $discount, 
        //  "discountperitem" => $discountperitem   
         "customer" => $customer, 
         "invoicesId" => $invoiceId,


        ],

    

        // ...
      ];
  
    if( $invoiceId > 0){


    
          



          //$request = (['prntReport => JOB_CARDS']);

       
            
              foreach( $invoiceItems as $invoiceItem) {

        $invoiceitem = new Invoice_item;
        $invoiceitem->invoicesId = $invoiceId;
        $invoiceitem->quantity = $request->quantity;
        $invoiceitem->unitId = $request->unit;
        $invoiceitem->price = $request->prize;
        $invoiceitem->productId = $request->product;
        $invoiceitem->stateId = 61;
        $invoiceitem->totalPrice = $request->total;
        $invoiceitem->VatType = $request->vat;
        $invoiceitem->Discount = $request->discount;
        $invoiceitem->vatAmnt= $request->vatperitem;
        $invoiceitem->save();
        

      
    }

    }
    //$unit = $request->product;

    // if($invoiceId > 0){

    //     $invoiceId = $request->invoiceId;
        
    // }else{

    //     $invoiceId = 0;

    // }

   



     $delivery = new Delivery;
     $delivery->docId = $request->productid;
     $delivery->customerId = $request->customer;
     $delivery->qnt = $request->quantity;
     $delivery->refrence = $request->reference;
     $delivery->productId = $request->product;
     $delivery->unitId = $request->unit;
     $delivery->stateId = 62;
     $delivery->invoiceNo = $invoiceId;
     $delivery->vehicleReg = $request->vehicle;
     $delivery->driver = $request->driver;
     $delivery->addressId = $request->address;
     $delivery->uniqu  =  $uniqueNumber ;
     $delivery->save();

     $createdIds[] = $delivery->id;

     

     $jobcarditemQNT = DB::table('order_items')->where('id',$id)->get();

     foreach(   $jobcarditemQNT as   $jobcarditemQNT){
         $qnt = $jobcarditemQNT->quantity;

     }
     

     DB::table('order_items')
             ->where('id',$id )
             ->update(['quantity' => $qnt - $delivery->qnt  ,
             'stateId' => 44 ]);

    


    $thresholdQuantity = 0;
   

    $complete = DB::table('order_items')
                   ->where('id',$id )
                   ->value('quantity');

    if($complete <= 0){

         $currentDate = Carbon::now();


        DB::table('order_items')
           ->where('id', $id )
           ->update(['stateId' => 45,
                     'DateComplete' => $currentDate             
                      ]);    

    }

    DB::table('pickingslips')
      ->where('orderitemId',$id )
      ->where('stateId',61 )
      ->update(['stateId' => 45  ]);





        $response = $uniqueNumber;
  
       return response()->json($response);


    

    }


   
}
