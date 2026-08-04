<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use App\Models\Orders;
use App\Models\JobCard;
use App\Models\Porduct;
use App\Models\Customer;
use App\Models\Jobcarditem;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;

class RestOrderListRptController extends Controller
{


    public function qry1(Request $request){

        $response = Customer::all();

        return($response);

    }





    public function qry2(Request $request){

        $response = Type::all();

        return($response);

        
    }

    public function qry3(Request $request){

        $productId = $request->get('productId');

        $response = Porduct::find($productId);

        return($response);

        
    }

    public function qry4(Request $request){

        $response = Porduct::all();

        return($response);
        
    }

    public function qry5(Request $request){
        

        $response = DB::table('orders')
                    ->orderBy('customerId', 'asc')
                    ->where('stateId','<>','134')
                    ->where('stateId','<>','45')
                    ->get(['id','customerId']);

        return($response);
        
    }

    public function qry6(Request $request){


        $orderId = $request->get('orderId');


        $response = DB::table('order_items')->where('ordersId',$orderId)                                         
                       ->where('quantity','>','0')
                       ->where('stateId','<>','45')
                       ->get();



        return($response);
        
        
    }

    public function qry7(Request $request){
        
        $productId = $request->get('productId');

        $response = Porduct::find($productId);
        
        return($response);
        
    }

    public function qry8(Request $request){
        
    }

    public function qry9(Request $request){
        
    }

    public function qry10(Request $request){
        
    }

    public function qry11(Request $request){
        
    }

    public function qry12(Request $request){
        
    }

    public function qry13(Request $request){
        
    }
}
