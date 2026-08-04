<?php

namespace App\Http\Controllers;


include_once base_path().'\App\Library\OrderListRpt.php';
//use App\Http\Controllers\PrintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Delivery;
use App\Models\TbDelivery;
use App\Models\TbDeliveryItem;
use App\Models\Invoices;
use App\Models\Invoice_item;
use App\Models\Poduct;
use App\Models\Order_item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PrintController;
use DB;
use OrderListRpt;
use Carbon\Carbon;
use Auth;
use Exception;


class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   

        $data['deliveries'] = Delivery::orderBy('id','desc')->paginate(2000);
        return view('deliveries.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

   



        
    $data = [

        'orderitemId' => $request->get('orderitem'),
        'orderId' =>$request->get('order'),

    ];


    
    $url = env('APP_URL');
    $maxRetries = 3; 
    $retryDelay = 2; 
    
    
    
    
    // $response = Http::get($url.'/qrydeliveries/store', $data);
    
    
    // if ($response->successful()) {
        
    //     $orderitems = $response->json()['orderitems']; 
    //     $orderinfo = $response->json()['orderinfo']; 

    //     //dd($orderinfo);
    
        
    //     View::share('orderitems', $orderitems);
    //     View::share('orderinfo', $orderinfo);
    
        
    //     return view('deliveries.create'); 
    // } else {
        
    //     dd('Sorry , there an error with your request');
    
    // }

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Make the HTTP request
            $response = Http::timeout(10) // Set a timeout of 10 seconds
                             ->retry(3, 1000) // Retry 3 times with a 1-second delay
                             ->get($url.'/qrydeliveries/store', $data);
    
            //$data['info'] = json_decode($response, true);
        
            // Check if the request was successful
            if ($response->successful() ){
              
                $orderitems = $response->json()['orderitems']; 
                $orderinfo = $response->json()['orderinfo']; 
        
                //dd($orderinfo);
            
                
                View::share('orderitems', $orderitems);
                View::share('orderinfo', $orderinfo);
            
                
                return view('deliveries.create'); 
    
            } else {
                // Throw an exception if the request fails
                return view('errorpage', [
                  'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
              ]);
    
            }
        } catch (Exception $e) {
            // Log the error
            Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
            return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);
    
            // If this is the last attempt, return an error message
            // if ($attempt === $maxRetries) {
            //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
            // }
    
            if ($attempt === $maxRetries) {
              return view('errorpage', [
                  'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
              ]);
          }
    
            // Wait before retrying
            sleep($retryDelay);
        }
    }

    //------------------------------------------------------------------------------------------------------------------------------


        $selectedValues = $request->input('selectedValues');
        $orderitemId = $request->get('orderitem');
        $orderId = $request->get('order');
        $selectedValues = $request->input('data');
        $dataArray = json_decode(urldecode($selectedValues), true);

     

        if (!empty($dataArray)) {



             $firstValue = $dataArray[0];

            
            //$id= Order_item::whereIn('id',$dataArray)->pluck('ordersId');
            $id = DB::table('order_items')->where('id', $firstValue)->pluck('ordersId');
            
         
           // $response = 89;

            $orderinfo  = DB::table('orders')->where('id',$id)->get();

          
          
            $orderitems = Delivery::whereIn('docId', $dataArray )->get();

            //Log::info(''.$orderitems);

           return view('deliveries.show',['orderitems'=> $orderitems,'orderinfo'=>$orderinfo ]);

             //return view('deliveries.create',[]);
            

            

        }

        


        
       

        if ($orderitemId)
        {
            $Id = DB::table('order_items')->where('id',$orderitemId)->pluck('ordersId');
            
         

            $orderinfo  = DB::table('orders')->where('id',$Id)->get();


            $orderitems = DB::table('order_items')->where('id', $orderitemId )->get();

            
            // echo "<pre>";
            // print_r($orderitems );
            // exit;


      

            View::share('orderitems',$orderitems);
            View::share('orderinfo',$orderinfo );



        }

        if ($orderId) {

            $orderinfo  = DB::table('orders')->where('id',$orderId)->get();

            $orderitems = DB::table('order_items')->where('ordersId',$orderId)->get();


           

            View::share('orderitems',$orderitems);
            View::share('orderinfo',$orderinfo );

        }

      



    

        return view('deliveries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            
    $url = env('APP_URL');
    $maxRetries = 3; 
    $retryDelay = 2; 
    

    $data = [

        'quantity' => $request->qnt,
        'orderitemdeduct' => $request->qnt,

    ];
    
    
    
    
   // $response = Http::get($url.'/qrydeliveries/store', $data);
    
    
    // if ($response->successful()) {

    //     $delivery = new Delivery;
    //     $delivery->vehicleReg = $request->vehicleReg;
    //     $delivery->driver = $request->driver;
    //     $delivery->unitId = $request->unitId;
    //     $delivery->qnt = $request->qnt;
    //     $delivery->invoiceNo = $request->invoiceNo;
    //     $delivery->productId = $request->productId;
    //     $delivery->addressId = $request->addressId;
    //     $delivery->barcode= $request->barcode;
    //     $delivery->refrence = $request->refrence;
    //     $delivery->other = $request->other;
    //     $delivery->userId = Auth::id();
    //     $delivery->stateId = $request->stateId;
    //     $delivery->save();

    //     return redirect()->route('deliveries.index')->with('success','Delivery has been created successfully.');
        
    // } else {
        
    //     dd('Sorry , there an error with your request');
    
    // }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            // Make the HTTP request
            $response = Http::timeout(10) // Set a timeout of 10 seconds
                             ->retry(3, 1000) // Retry 3 times with a 1-second delay
                             ->get($url.'/qrydeliveries/store', $data);
    
            
        
            // Check if the request was successful
            if ($response->successful() ){
              
                $delivery = new Delivery;
                $delivery->vehicleReg = $request->vehicleReg;
                $delivery->driver = $request->driver;
                $delivery->unitId = $request->unitId;
                $delivery->qnt = $request->qnt;
                $delivery->invoiceNo = $request->invoiceNo;
                $delivery->productId = $request->productId;
                $delivery->addressId = $request->addressId;
                $delivery->barcode= $request->barcode;
                $delivery->refrence = $request->refrence;
                $delivery->other = $request->other;
                $delivery->userId = Auth::id();
                $delivery->stateId = $request->stateId;
                $delivery->save();




                    $delivery = new TbDelivery;
                    $delivery->reference           =$request->reference;
                    $delivery->customerId          = $request->customerId;
                    $delivery->vehicleReg          =  $request->vehicleReg;
                    $delivery->driver              =  $request->driver;
                    $delivery->invoiceNo           =  $request->invoiceNo;
                    $delivery->addressId           = $request->addressId;
                    $delivery->save();


                    $deliveryitem = new TbDeliveryItem;
                    $deliveryitem->productId                = $request->productId;
                    $deliveryitem->unitId                   =  $request->unitId;
                    $deliveryitem->deliveryId               = $delivery->id;
                    $deliveryitem->unitId                   = $request->unitId;
                    $deliveryitem->quantity                 = $request->qnt;
                    $deliveryitem->save();
        
                return redirect()->route('deliveries.index')->with('success','Delivery has been created successfully.');
    
            } else {
                // Throw an exception if the request fails
                return view('errorpage', [
                  'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
              ]);
    
            }
        } catch (Exception $e) {
            // Log the error
            Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
            return view('errorpage', [
              'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
          ]);
    
            // If this is the last attempt, return an error message
            // if ($attempt === $maxRetries) {
            //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
            // }
    
            if ($attempt === $maxRetries) {
              return view('errorpage', [
                  'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
              ]);
          }
    
            // Wait before retrying
            sleep($retryDelay);
        }
    }
     



    }
        

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        return view('deliveries.show'); 
        // $selectedValues = $request->input('data');
        // $dataArray = json_decode(urldecode($selectedValues), true);

     

    
             //$firstValue = $dataArray[0];

            
            //$id= Order_item::whereIn('id',$dataArray)->pluck('ordersId');
            //$id = DB::table('order_items')->where('id', 149)->pluck('ordersId');
            
         
           // $response = 89;

            //$orderinfo  = DB::table('orders')->where('id',149)->get();

          
          
            //$orderinfo  = DB::table('orders')->where('id',149)->get();

          //  $orderitems = DB::table('order_items')->where('ordersId',149)->get();

           // Log::info(''.$orderitems);

          // return view('deliveries.show',['orderitems'=> $orderitems,'orderinfo'=>$orderinfo ]);

             //return view('deliveries.create',[]);


            //  $data = [

            //     //'orderitemId' => $request->get('orderitem'),
            //     'orderId' =>149,
        
            // ];
        
        
            
            // $url = env('APP_URL');
            
            
            
            
            // $response = Http::get($url.'/qrydeliveries/store', $data);
            
            
            // if ($response->successful()) {
                
            //     $orderitems = $response->json()['orderitems']; 
            //     $orderinfo = $response->json()['orderinfo']; 
        
            //     //dd($orderinfo);
            
                
            //     View::share('orderitems', $orderitems);
            //     View::share('orderinfo', $orderinfo);
            
                
            //     return view('deliveries.show'); 
            // } else {
                
            //     dd('Sorry , there an error with your request');
            
            // }
            

            

        

        return view('deliveries.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('deliveries.edit',compact('delivery'));
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
        $request->validate([
        'name' => 'required',
        'email' => 'required',
        'address' => 'required',
        ]);
        $delivery = Delivery::find($id);
        $delivery->name = $request->name;
        $delivery->email = $request->email;
        $delivery->address = $request->address;
        $delivery->save();
        return redirect()->route('deliveries.index')
        ->with('success','Delivery Has Been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delivery->delete();
        return redirect()->route('deliveries.index')
        ->with('success','Delivery has been deleted successfully');
    }

        // Other resource methods (index, store, show, etc.)

    /**
     * Display the deliveries page.
     *
     * @return \Illuminate\Http\Response
     */
    public function display()
    {
        return view('deliveries.display'); // Return the appropriate view
    }

    public function collect(Request $request){

        $response = 78;




       return response()->json($response);




}
        

    public function invoice(Request $request)
    {


        $tt = null;

      

        $customer   =  $request->customer;
        $vehicleId  =  $request->vehicleId;
        $addressId  =  $request->addressId;
        $driverId   =  $request->driverId;

         $invoice = new Invoices;
         $invoice->customerId = $customer;
         $invoice->stateId = 61;
         $invoice->save();

         $response =  $invoice->id;

         return response()->json($response);


    }

   
 public function delivernote(Request $request)
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

    
   

    

    $trimmedString = explode(',',$pric)[0];
    $price = trim($trimmedString);
    $uniqueNumber = $request->uniqueNumber;
    $vat = $request->vat;
    $vatperitem = $request->vatperitem;
    $totalwthotvat = $request->totalwithVat;


 
    

    
   
    
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

   

            
    $url = env('APP_URL');

    $data = [

        'quantity' => $request->quantity,
        'orderitemdeduct' => $id,

    ];
    
    
    
    
    $response = Http::get($url.'/qrydeliveries/store', $data);

 
    
    if (!$response->successful()) {
        dd([
            'message' => 'The response failed.',
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }    



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


                    $delivery = new TbDelivery;
                    $delivery->customerId          = $request->customer;
                    $delivery->vehicleReg          =  $request->vehicle;
                    $delivery->driver              =  $request->driver;
                    $delivery->invoiceNo           =  $request->invoiceId;
                    $delivery->addressId           = $request->addressId;
                    $delivery->save();


                    $deliveryitem = new TbDeliveryItem;
                    $deliveryitem->productId                = $request->productid;
                    $deliveryitem->unitId                   =  $request->unit;
                    $deliveryitem->deliveryId               = $delivery->id;
                    $deliveryitem->quantity                 = $request->quantity;
                    $deliveryitem->save();
        

     $createdIds[] = $delivery->id;


     

    //  $jobcarditemQNT = DB::table('order_items')->where('id',$id)->get();

    //  foreach(   $jobcarditemQNT as   $jobcarditemQNT){
    //      $qnt = $jobcarditemQNT->quantity;

    //  }
     

    //  DB::table('order_items')
    //          ->where('id',$id )
    //          ->update(['quantity' => $qnt - $delivery->qnt  ,
    //          'stateId' => 44 ]);

    


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

 public function delivernote1(Request $request)
    {

            //DB::beginTransaction();
            
            foreach ($request->items as $item) {

                $invoiceValue = $item['invoiceId'];

 



                             
                Delivery::create([

                    'docId'          => 0,
                    'customerId'     => $item['customerId'],
                    'productId'      => $item['productId'],
                    'unitId'         => $item['unitTypeId'],
                    'qnt'            => $item['quantity'],
                    'addressId'      => $item['addressId'],
                    'vehicleReg'     => $item['vehicleId'],
                    'driver'         => $item['driverId'],
                    'invoiceNo'      => $item['invoiceId'],
                    'stateId'        => 62,
                    'uniqu'          => $item['uniqueNumber']

                ]);

                if ($invoiceValue > 0){

                  
                    
                Invoice_item::create([

                    'productId'      => $item['productId'],
                    'unitId'         => $item['unitTypeId'],
                    'quantity'       => $item['quantity'],
                    'vatAmnt'        => $item['vat'],
                    'Discount'       => $item['discount'],
                    'price'          => $item['price'],
                    'totalPrice'     => $item['total'],
                    'invoicesId'     => $item['invoiceId'],
                    'stateId'        => 64

                ]);

                }


                $uniqueNumber =  $item['uniqueNumber'];
            }



            
            DB::commit();
            
            $response = $uniqueNumber;

           

               return response()->json($response);

            
 
    }


 public function deliver(Request $request)
 {

  
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

 //dd('QNQNQN');

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
    //Log::info("Invoicefffffffffs ------------------------------------------- : ".$price );
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
        $invoiceitem->userId = Auth::id();
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

   



     $delivery = Delivery::find($id);
     $delivery->invoiceNo = $invoiceId;    
     $delivery->save();

     $createdIds[] = $delivery->id;

     //Log::info($createdIds);

      $response =$invoiceId;;

     // Log::info($response );

    //   $tt = null;
    //   $tt = new OrderListRpt();
    //   return $tt->order($request);
        

     return response()->json($response);
    
 }

 

    

 public function fetch(Request $request){



    Log::info('ndirikupinda ');

    dd('Reached get_suggestions method');

    return response()->json($response);

  }







public function getProductbyidForOrderItem(Request $request){



    $products = Porduct::all();

    $productData = $products->map(function ($product) {

        return ["productName" => $product->name,

                "product" => $product->id,



    ];

    });



    $charactr = $request->productname;

    Log::info( '----------'.$charactr);



    $query = request()->query('query', '');

    Log::info( $query);

    $suggestions = $productData->filter(function ($product) use ($charactr ) {

        return strpos(strtolower($product['productName']), strtolower( $charactr )) !== false;

    })->values();



    //Log::info($suggestion);



    return response()->json($suggestions);



  }





  public function get_suggestions(Request $request){


    $orderItemId = $request->id ;

    $orderItem = Order_item::find($orderItemId);

    if ($orderItem) {

        $orderItem->delete();

        $response = $orderItemId;
        
         return response()->json($response);

    } 


  

  }











public function order (Request $request)

{

    $user = auth()->user()->id;





    $user = Users::where('id', $user)->value('company');



    if($user){









    $orderitems = $request->input('productsArray');







    if(!$request->input('productsArray')) {

        $response = [

            'error' => 'The productsArray is null.',

        ];

    

        return response()->json($response, 400); 

    }






    $order = new Orders;

    $order->reference = 'online';

    $order->date = now();

    $order->other = 'now';

    $order->customerId = $user;

    $order->totalValue = 0.00;

    $order->datePlaced = now();

    $order->dueDate= now();

    $order->stateId = 61;

    $order->orderBy = 1;

    $order->userId = auth()->user()->id ;

    $order->save();



    foreach ( $orderitems as $orderitem){



        $id = $orderitem['productId'];

        





        $pack = Porduct::where('id',$id)->value('unitPackId');

        $qnt = $orderitem['quantity'] ;

        $price = $orderitem['pricePerBale'] ;







    $orderitem = new Order_item;

    $orderitem->ordersId = $order->id;

    $orderitem->customerId = $order->customerId;

    $orderitem->quantity =   $qnt;

    $orderitem->other = 'online';

    $orderitem->unitId =   $pack;

    $orderitem->price =   $price;

    $orderitem->openningQNT = $qnt;

    //$orderitem->dueDate = today();

    $orderitem->reference = 'online';

    $orderitem->productId =   $id ;

    $orderitem->stateId = 61;

    $orderitem->orderBy = 1;

    $orderitem->totalPrice = $request->totalPrice;

    $orderitem->userId = auth()->user()->id ;

    $orderitem->save();



    }

    

    // Process the $productsArray as needed



    return response()->json(['message' => 'Data receivedpppppppp successfully']);





       }else{



        $response = 500;





        return response()->json($response); 



}





}





public function getProductbyidForOrderIte(Request $request){







    $id = $request->productname;

    $product = Porduct::find($id);



    if ($product) {





        $pack = $product->unitPackId;

        $packet = $product->unitTypeId;

        $colour = $product->color;

        $material = $product->materialTypeId;

        $length = $product->product_length;

        $width = $product->product_Width;

        $price = $product->defaultSellingPice; 

        $gusset = $product->gussetWidth; 

        $bagtype = $product->bagType;

       

    }







    $pack= Type::where('id', $pack)->value('name');

    $packet = Type::where('id',  $packet)->value('name');

    $colour = Type::where('id',  $colour )->value('name');

    $material = Type::where('id', $material)->value('name');

    $bagtype = Type::where('id',  $bagtype)->value('name');





    $product_price = 20;



    





    if($product_price){



        $price = $product_price;



    }else{



        $price = $price;





    }







    $suggestions = [

        'pack' => $pack,

        'packet' => $packet,

        'colour' => $colour,

        'material' => $material,

        'length' =>  $length,

        'width' =>  $width ,

        'price' => $price,

        'gusset' => $price,

        'bagtype' => $bagtype,

        'id' => $id,

    ];



    





    return response()->json($suggestions);



  }



  public function deleteorderitem(Request $request) {

    
    $responsed = $request->productname;



    return response()->json($response);

}







  



  public function getProduct(Request $request) {

    $query = $request->query('query', '');

    $quantity = $request->query('quantity', '');



    $product = Porduct::where('name', $query)->first();



    if ($product) {

        $product_name = $product->name;

        $product_id = $product->id;



        if ($product->costPrice !== null) {

            $product_price = (float) $product->costPrice;



            try {

                $quantity = (int) $quantity;

                $total_cost = $product_price * $quantity;



                return response()->json([

                    "product_name" => $product_name,

                    "product_price" => $product_price,

                    "total_cost" => $total_cost,

                    "quantity" => $quantity,

                    "productId" => $product_id,

                ]);

            } catch (Exception $e) {

                return response()->json([

                    "product_name" => $product_name,

                    "product_price" => 0,

                    "total_cost" => 0,

                    "quantity" => 0,

                    "productId" => 0,

                ]);

            }

        } else {

            return response()->json([

                "product_name" => $product_name,

                "product_price" => 0,

                "total_cost" => 0,

                "quantity" => 0,

                "productId" => 0,

            ]);

        }

    } else {

        return response()->json([

            "product_name" => $product_name,

            "product_price" => 0,

            "total_cost" => 0,

            "quantity" => 0,

            "productId" => 0,

        ]);

    }

}



















public function getSuggestion() {

    $products = Porduct::all();

    $productData = $products->map(function ($product) {

        return ["productName" => $product->name];

    });



    $query = request()->query('query', '');

    $suggestion = $productData->filter(function ($product) use ($query) {

        return strpos(strtolower($product['productName']), strtolower($query)) !== false;

    })->values();



    return response()->json($suggestion);

}





public function getProductbyidFor(Request $request){



    $productid = $request->productid;





 //Log::info('ndirikupinda '.$productid );



    $newPricing = 20;
    

    $unit = DB::table('porducts')
              ->where('id', $productid)
              ->value('unitPackId');

    Log::info('unit yacho  '.$unit );



    

    if ($newPricing !== null) {

        $values = [

            "unitTypeId" => $unit,

            "price"      => $newPricing,

        ];

    } else {

        $values = [

            "unitTypeId" => $unit,

            "price"      => $old,

        ];

    }

    

    $response['data'] = $values;

    

    return response()->json($response);

    

    



}






public function changeOnlineProductstate(Request $request){

         
            $data = [

                'productId' => $request->get('productId'),
                'statusId' =>$request->get('statusId'),

                                ];


            $url = env('APP_URL'); 

            $maxRetries = 3; // Maximum number of retries
            $retryDelay = 2; // Delay between retries in seconds

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {

                try {
                    // Make the HTTP request
                    $response = Http::timeout(10) // Set a timeout of 10 seconds
                                      ->retry(3,1000) // Retry 3 times with a 1-second delay
                                      ->get($url.'/qrycustomeronlineproducts/index', $data); // Use POST for storing data


                 
                    // Check if the request was successful
                    if ($response->successful) {

                        //dd('hoyoooooooooo');

                        return response()->json([
                            'success' => true,
                            'message' => 'Orders has been created successfully.',
                            'data' => $response->data ?? null
                        ], 200);
                    
                      
              
                    } else {
                        // Throw an exception if the request fails
                        return view('errorpage', [
                        'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
                    ]);

                    }
                } catch (Exception $e) {
                    // Log the error
                    Log::error('Attempt  on storing new order ' . $attempt . ' failed: ' . $e->getMessage());
                    return view('errorpage', [
                    'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
                ]);

                    // If this is the last attempt, return an error message
                    // if ($attempt === $maxRetries) {
                    //     return dd('Sorry, there was an error with your request after ' . $maxRetries . ' attempts.');
                    // }

                    if ($attempt === $maxRetries) {
                    return view('errorpage', [
                        'message' => 'Sorry, there was an error with your request after ' . $maxRetries . ' attempts.'
                    ]);
                }

                    // Wait before retrying
                    sleep($retryDelay);
                }
            }



}


}
