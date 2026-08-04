<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

class RestProductsController extends Controller
{
    public function index(Request $request)
    {


        return response()->json($data);
    }




    public function store(Request $request)
    {



           return response()->json($responds);
    }




    public function show(Request $request)
    {


        return response()->json($response);
    }




    public function update(Request $request, $id)
    {
        $response = 1 ;
                
        return response()->json( $response);
    }




    public function destroy(Request $request)
    {

                
            return response()->json( $response);



    

    }
}
