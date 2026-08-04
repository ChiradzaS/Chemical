<?php

namespace App\Http\Controllers;
use App\Models\Machinery;

use Illuminate\Http\Request;

class RestMachineController extends Controller
{
    public function index(Request $request)
    {

       // Log::info('hoyoooo');
        
       $machineType =  $request->input('machine');
       
       if ( $machineType  == 'machineEx' ){
           
        $data['machineries'] = Machinery::where('name', 'like', 'ex%')->orderBy('id', 'asc')->get();
        
     
        return response()->json($data);
           
       }else{
           
           
           
        $data['machineries'] = Machinery::where('name', 'not like', 'ex%')->orderBy('id', 'asc')->get();


        return response()->json($data);
           
       };

        
        
    }
}
