<?php

namespace App\Http\Controllers;

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

class RestOrderItemsController extends Controller
{

    public function index(Request $request)
    {

        $search = $request->input('search');

         

            if( $search){

                $customerId = $request->input('customerId');
       
                $productId = $request->input('productId');
            
                $toDate = $request->input('toDate');
             
                $fromDate = $request->input('fromDate');
            
                $customerComp = $request->input('customerComp');
            
                $productComp = $request->input('productComp');



                $data = Order_item::select('*')
                                              ->whereDate('created_at', '<=', $toDate)
                                                    ->whereDate('created_at', '>=', $fromDate)
                                                    ->where('productId', $productComp, $productId)  
                                                    ->where('customerId', $customerComp, $customerId)  
                                                    ->where('stateId', '<>', 45)  
                                                    ->orderBy('updated_at','desc')
                                                    ->get();

                return response()->json($data);
            }
    
        


      $data = Order_item::select('*')->orderBy('updated_at','desc')->where('stateId', '<>', 45)->get();

        return response()->json($data);
    }




    public function store(Request $request)
    {

       // Log::info('RestOrderItemsController@store: Start');

        $orderitem = new Order_item;
        $orderitem->ordersId = $request->input('ordersId');
        $orderitem->customerId  = $request->input('customerId');
        $orderitem->quantity  = $request->input('quantity');
        $orderitem->other  = $request->input('other') ?? 'none';
        $orderitem->unitId  = $request->input('unitId');
        $orderitem->price  = $request->input('price');
        $orderitem->openningQNT = $request->input('quantity');
        $orderitem->dueDate  = $request->input('dueDate')?? now()->toDateString();
        $orderitem->reference  = $request->input('reference')?? 'none';
        $orderitem->productId  = $request->input('productId');
        $orderitem->stateId = 61;
        $orderitem->orderBy = 0;
        //$orderitem->totalPrice  = $request->input('totalPrice') ?? '0.00';
        $orderitem->userId =  $request->input('userId');
        $orderitem->save();

        $response =  $orderitem->ordersId ;

        return response()->json($response);
    }

    public function show(Request $request)
    {
        $order = $request->input('id');

        $response = DB::table('order_items')->where('id', $order)->get();

     

       return response()->json($response);
    }

    public function update(Request $request)
    {

        $data = $request->data;

        $id = $request->input('id');
        $ids = $request->input('order');

    

       


        $order_item = Order_item::find($id);
        $order_item->ordersId =   $ids;
        $order_item->quantity =  $request->input('quantity');
        $order_item->other =  $request->input('other');
        $order_item->unitId =  $request->input('unitId');
        $order_item->price =  $request->input('price');
        $order_item->dueDate =  $request->input('dueDate');
        $order_item->openningQNT =  $request->input('quantity');
        $order_item->reference =  $request->input('reference');
        $order_item->productId =  $request->input('productId');
        $order_item->stateId = 44;
        $order_item->totalPrice = $request->input('totalPrice ');;
        $order_item->save();

    

        $response =  1;

        return response()->json($response);
    }

    public function destroy(Request $request)
    {

        $data = $request->input('id');
        

       
       $result = DB::table('order_items')
                    ->where('id', $data)
                    ->delete();
   

                    
        $response = 1;

        return response()->json($response);
    }


    public function changestate(Request $request)
    {
        $id = $request->input('id');
    
        $orderItem = Order_item::find($id);
        if ($orderItem) {
            $orderItem->stateId = 44;
            $orderItem->save();
    
            return response()->json(['success' => true, 'message' => 'State updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Order item not found']);
        }
    }
}
