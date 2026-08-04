<?php

namespace App\Http\Controllers;

use App\Models\Machinery;
use App\Models\Porduct;
use App\Models\Type;
use App\Models\Users;
use App\Models\Orders;
use App\Models\CustomerPrice;
use App\Models\ProductPricing;
use App\Models\Order_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use DB;

class QryRestController extends Controller
{
    
    public function qryType(Request $request)
    {
        //dd('WOWOWOWO');
        $qry = $request->get('type');
        if($qry == 'shift') 
        {
            $types = Type::select('name')
                         ->where('groupType', 'shift')
                         ->get();
            //$jsonData = json_encode($machinery);
	        //return $jsonData;
            return response()->json($types);
        }
        $types = new Type();
        return response()->json($types);  
    } 


    public function qryMachinery(Request $request)
    {
        $qry = $request->get('qry');
        if($qry == 'list') 
        {
            $machinery = Machinery::select('name')->get();
            //$jsonData = json_encode($machinery);
	        //return $jsonData;
            return response()->json($machinery);
        }
        $machinery = new Machinery();
        return response()->json($machinery);  
    } 

    public function qryProduct(Request $request)
    {
        $tmpId = $request->get('id');
        $jobCard = Porduct::find($tmpId);
        if (!$jobCard) {
            return response()->json(['error' => 'Product not found in database.'], 404);
        }
        return response()->json($jobCard); 
    }




    public function qryUsers()
    {

        //Log::info('OOOOOOOOOOOOO');
        // $srchvalue = $request->get('custid');
        // $srchvalue = intval($srchvalue);

        $id = DB::table('types')
                ->where('name' ,'=', 'customer')
                ->where('groupType' ,'=', 'user')
                ->value('id');

        $users = DB::table('users')
            ->where('userType', $id)
             ->orderBy('updated_at','asc')
            ->get();

  

        $response = $users ;

 
        return response()->json($response);      
    }        





    public function qrygetProductbyidFor(Request $request){

        $productid = $request->get('productid');



        $newPricing = ProductPricing::where('productId', $productid)
            ->where('active', '=', '1')
            ->value('price');
        
        $unit = Porduct::where('id', $productid)->value('unitPackId');
        $old = Porduct::where('id', $productid)->value('defaultSellingPice'); // Corrected variable name
        
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


    public function allocatecustomer(Request $request)
    {

        $user = $request->user;
        $customer = $request->CustomerId;

        Users::where('id', $user)->update(['company' => $customer]);

        $response = 88;
      
        return response()->json($response);
    }



    
    public function customerProducts(Request $request)
    {

        $data = $request->data;

            $product = new CustomerPrice;
            $product->userId = $data['userId'];
            $product->price = $data['price'];
            $product->Twidth =  $data['Twidth'];
            $product->gusset =  $data['gusset'];
            if( $data['gusset'] == null){
     
             $gusset = 0;
     
             }
            $product->width = $data['width'];
            $product->length  =  $data['length'];
            $product->micron = $data['micron'];
            $product->material = $data['materialType'];
            $product->colour  =  $data['colour'];
            $product->bagType  =  $data['bagType'];
            $product->save();

      
        return response()->json($response);
    }



    public function qrygetnewproducts(){

      

      $products  = CustomerPrice::select('*')->get();


        return response()->json($products);


    }

    public function qrygetallorders(){

        $data  = Orders::select('*')->get();


        return response()->json($data);

    }



    public function store1(Request $request)
{

        Log::info('the sky');

        $data = $request->data;

        Log::info($data);

        $order = new Orders;
        $order->reference = $data('reference');
        $order->date = $data('date');
        $order->other = $data('other');
        $order->customerId = $data('customerId');
        $order->totalValue = $data('totalValue');
        $order->datePlaced = $data('datePlaced');
        $order->dueDate= $data('dueDate');
       // $order->value = $data('value');
        $order->stateId = 61;
        $order->userId = Auth::id();
        $order->save();

        $orderitem = new Order_item;
        $orderitem->ordersId = $order->id;
        $orderitem->customerId = $data('customerId');;
        $orderitem->quantity = $data('quantity');
        $orderitem->other = $data('order_item_other');
        $orderitem->unitId = $data('unitId');
        $orderitem->price = $data('price');
        $orderitem->openningQNT = $data('openningQNT');
        $orderitem->dueDate = $data('dueDate');
        $orderitem->reference = $data('reference');
        $orderitem->productId = $data('productId');
        $orderitem->stateId = 61;
       // $orderitem->value = $data('value');
        $orderitem->totalPrice = $data('totalPrice');
        $orderitem->userId = Auth::id();
        $orderitem->save();

        Log::info('savedddd');

        return response()->json($order->id);

}




      public function  qrystore(Request $request){
    

       
       

        $data = $request->data;
        
        Log::info($data);

        

           $responds = 100;



           return response()->json( $responds);

}

}