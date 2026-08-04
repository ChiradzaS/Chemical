<?php

namespace App\Http\Controllers;
use App\Models\Orders;
use App\Models\Order_item;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;
use Carbon\Carbon;

class RestOrdersController extends Controller
{


    public function index(Request $request)
    {
        $search = $request->input('search');

  
        $currentYear = Carbon::now()->year;

        
        if($search){

            $customerId = $request->input('customerId');
   
            $productId = $request->input('productId');

            $toDate = $request->input('toDate');
         
            $fromDate = $request->input('fromDate');
        
            $customerComp = $request->input('customerComp');



            
        
          


            $data = Orders::join('order_items', 'orders.id', '=', 'order_items.ordersId')
            ->select(
    
                'orders.id as orders_id',
                'orders.customerId as orders_customerId',                       
                'orders.datePlaced as orders_datePlaced',
                //'orders.dueDate as orders.dueDate',
                'orders.created_at as orders_created_at',
                'orders.stateId as orders_stateId',
                'order_items.id as order_items_id',                            
                'order_items.productId as order_items_productId',
                'order_items.unitId as order_items_unitId',
                'order_items.quantity as order_items_quantity',
                'order_items.openningQNT as order_items_openningQNT',
                'order_items.stateId as order_items_stateId',
                'order_items.dueDate as order_items_dueDate',
                'order_items.created_at as order_items_created_at',
                'order_items.orderBy as order_items_orderBy',
                'order_items.manufactured as order_items_manufactured'
    
            )
            ->groupBy(
                'orders.id', 
                'orders.customerId',                      
                'orders.datePlaced', 
                'orders.created_at',
                'orders.stateId', 
                'order_items.id',
                'order_items.productId', 
                'order_items.unitId',
                'order_items.quantity', 
                'order_items.stateId', 
                'order_items.dueDate', 
                'order_items.created_at',
                'order_items.openningQNT',
                'order_items.orderBy', 
                'order_items.manufactured'
        
            )
            ->whereDate('orders.created_at', '<=', $toDate)
            ->whereDate('orders.created_at', '>=', $fromDate) 
            ->where('orders.customerId', $customerComp, $customerId)  
            ->where('orders.stateId', '<>','45')
            ->orderBy('orders.updated_at', 'desc')
            ->get();

            //Log::info($data);
                                       
                                       

            return response()->json($data);
        }


        $data = Orders::join('order_items', 'orders.id', '=', 'order_items.ordersId')
        ->select(

            'orders.id as orders_id',
            'orders.customerId as orders_customerId',                       
            'orders.datePlaced as orders_datePlaced',
            //'orders.dueDate as orders.dueDate',
            'orders.created_at as orders_created_at',
            'orders.stateId as orders_stateId',
            'order_items.id as order_items_id',                            
            'order_items.productId as order_items_productId',
            'order_items.unitId as order_items_unitId',
            'order_items.quantity as order_items_quantity',
            'order_items.openningQNT as order_items_openningQNT',
            'order_items.stateId as order_items_stateId',
            'order_items.dueDate as order_items_dueDate',
            'order_items.created_at as order_items_created_at',
            'order_items.orderBy as order_items_orderBy',
            'order_items.manufactured as order_items_manufactured'

        )
        ->groupBy(
            'orders.id', 
            'orders.customerId',                      
            'orders.datePlaced', 
            'orders.created_at',
            'orders.stateId', 
            'order_items.id',
            'order_items.productId', 
            'order_items.unitId',
            'order_items.quantity', 
            'order_items.stateId', 
            'order_items.dueDate', 
            'order_items.created_at',
            'order_items.openningQNT',
            'order_items.orderBy',
            'order_items.manufactured'
 
        )
            ->where('orders.stateId', '<>','45')
            ->orderBy('orders.updated_at', 'desc')
            //->whereYear('orders.created_at', $currentYear)
            ->get();


         
        //Log::info($data);


        return response()->json($data);
    }




    public function store(Request $request)
    {


        $dataString = $request->query('data');

        $orderData = json_decode(urldecode($dataString), true);
               
        $orderData = $orderData['formData'] ?? null;
               

                if ($orderData) { 


                    Log::info($orderData);


                }

        $data = $request->input('customerId');

       // Log::info($request->input('customerId'));

        $order = new Orders;
        $order->date = $request->input('date')?? now()->toDateString();
        $order->other  = $request->input('other')?? now()->toDateString();
        $order->customerId = $request->input('customerId');
        $order->totalValue = $request->input('totalValue') ?? '0.00';
        $order->datePlaced = $request->input('datePlaced') ?? now()->toDateString();
        $order->dueDate = $request->input('dueDate') ?? now()->toDateString();
        $order->orderBy = 0;
        $order->stateId = 61;
        $order->userId = $request->input('userId');
        $order->save();


        
        $orderitem = new Order_item;
        $orderitem->ordersId = $order->id;
        $orderitem->customerId  = $request->input('customerId');
        $orderitem->quantity  = $request->input('quantity');
        $orderitem->other  = $request->input('other') ?? 'none';
        $orderitem->unitId  = $request->input('unitId');
        $orderitem->price  = $request->input('price');
        $orderitem->openningQNT =$request->input('quantity');
        $orderitem->dueDate  = $request->input('dueDate')?? now()->toDateString();
        $orderitem->reference  = $request->input('reference')?? 'none';
        $orderitem->productId  = $request->input('productId');
        $orderitem->stateId = 61;
        $orderitem->orderBy = 0;
        $orderitem->totalPrice  = $request->input('totalPrice') ?? '0.00';
        $orderitem->userId =  $request->input('userId');
        $orderitem->save();
        
        
    
           $responds = $order->id ;



           return response()->json($responds);
    }




    public function show(Request $request)
    {

        $orderitem = $request->input('itemid');

        if(  $orderitem > 0){

            $response =DB::table('order_items')->where('ordersId', $orderitem)->get();
           //$response =Order_item::select('*')->where('ordersId', $orderitem)->get();

            return response()->json( $response);


        }

       $order = $request->input('id');

       
       $response = DB::table('orders')->where('id', $order)->get();


       return response()->json($response);
    }




    public function update(Request $request)
    {

        $orderitem = $request->input('itemid');

        if(  $orderitem > 0){

            $response =DB::table('order_items')->where('ordersId', $orderitem)->get();
           //$response =Order_item::select('*')->where('ordersId', $orderitem)->get();

            return response()->json( $response);


        }

       $order = $request->input('id');

       
       $response = DB::table('orders')->where('id', $order)->get();


        return response()->json($response);
    }







    public function destroy(Request $request)
    {

        
        $data = $request->input('id');
     
            // $result = DB::table('orders')
            //             ->where('id', $data)
            //             ->update(['stateId' => 45]);


        $result = DB::table('orders')
                        ->where('id', $data)
                        ->delete();

                  DB::table('order_items')
                        ->where('ordersId', $data)
                        ->delete();

                        
        $response = 1;
                
        return response()->json( $response);



    

    }


    public function showitem(Request $request)
    {
        $order = $request->input('id');


        $response = DB::table('order_items')->where('id', $order)->get();

       

     

       return response()->json($response);
    }



    
    public function production(Request $request)
    {
        $orderId     = $request->input('id');
        $qnt         = (int) $request->input('qnt');
        $outstanding = (int) $request->input('outstanding');


        $manufactured = $outstanding - $qnt;
    
        // Update and return in one query using Laravel's query builder
        DB::table('order_items')
            ->where('id', $orderId)
            ->update(['manufactured' => $manufactured]);
    
        // Manually return the known updated data
        return response()->json([
            'id' => $orderId,
            'manufactured' => $manufactured,
        ]);
    }
    
    




    
}
