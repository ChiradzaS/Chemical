<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class RestCustomerController extends Controller
{
    
    public function index(Request $request)
    {

        $customer = $request->get('customer');
        
        if( $customer <> null){

            $data = Customer::where('id', $customer)->value('name');    
          
            return response()->json($data);

        }


       
    }

    public function store(Request $request)
    {

        

        $customer = new Customer;
        $customer->name = $request->input('name');
        $customer->customerType  = $request->input('customerType');
        $customer->save();

        $data = $customer->id;

        return response()->json($data);


    }

}
