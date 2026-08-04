<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Type;
use App\Models\Machinery;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use DB;

class RestTypesController extends Controller
{
    public function index(Request $request)
    {

 


        

        $type = $request->get('type');
        $machine = $request->get('machine');

        
       


        $name = $request->get('name');
        $customer = $request->get('customer');
        
        if( $customer <> null){

            $data = Customer::where('id', $customer)->value('name');    
 
          
            return response()->json($data);

        }

        if( $name <> null){

            $data = Type::where('id', $name)->value('name');    
    
            return response()->json($data);


        }
        

        if( $machine <> null){



            $machine = $request->input('machine');
            

            if ($machine === 'machineBagTop') {
                $data = Machinery::select('*')
                    ->where('processId', 24)
                    ->where('description', 'top')
                    ->orderBy('id', 'asc')
                    ->get();
            } elseif ($machine === 'machineBagBottom') {
                $data = Machinery::select('*')
                    ->where('processId', 24)
                    ->where('description', 'btm')
                    ->orderBy('id', 'asc')
                    ->get();
            } elseif ($machine === 'machineExTop') {
                $data = Machinery::select('*')
                    ->where(function ($query) {
                        $query->where('name', 'like', 'ex%')
                            ->orWhere('name', 'like', 'pe%')
                            ->orWhere('name', 'like', 'dr%');
                    })
                    ->where('description', 'top')
                    ->orderBy('id', 'asc')
                    ->get();
            } elseif ($machine === 'machineExBottom') {
                $data = Machinery::select('*')
                    ->where(function ($query) {
                        $query->where('name', 'like', 'ex%')
                            ->orWhere('name', 'like', 'pe%');
                    })
                    ->where('description', 'btm')
                    ->orderBy('id', 'asc')
                    ->get();
            } else {
                $data = Machinery::select('*')
                    ->orderBy('id', 'asc')
                    ->get();
            }


            

            return response()->json($data);

        }

        if( $type <> null) {

            $data = Type::select('*')->where('groupType', $type)->get();    
            //Log::info('seaching for the type ');
          
            return response()->json($data);
            
        }


        $action = $request->get('action');

        if( $action <> null && trim($action, ' ') == 'query'){
    
            $searchTerm = $request->input('searchInput');
    
    
        
         
           
          
            $customerIdComp = '<>';
            if ( $searchTerm <> null) {
             
              $customerIdComp = 'Like';
            } 
        
           
          
              
          
            $data = Type::select('*')->where('name',''.$customerIdComp,'%'.$searchTerm.'%')->get();    
          
                                          return response()->json($data);
            }

                    
        $data = Type::select('*')->orderBy('groupType','asc')->paginate(1000);
        return response()->json($data);

       
    }


    public function store(Request $request)
    {

        

        $type = new Type;
        $type->name = $request->input('name');
        $type->description  = $request->input('description');
        $type->value  = $request->input('value');
        $type->level  = $request->input('level') ;
        $type->parentKey  = $request->input('parentKey');
        $type->groupType  = $request->input('groupType');
        $type->topValue = $request->input('topValue') ?? 0;
        $type->childType  = $request->input('childType');
        $type->userId  = $request->input('userId');
        $type->start_time  = $request->input('start_time');
        $type->end_time  = $request->input('end_time');
        $type->label  = $request->input('lable')?? 0;
        $type->save();

        $data = $type->id;

        return response()->json($data);


    }

    public function customer(Request $request)
    {

        $customer = $request->input('customer');
        
        $data = DB::table('customers')->where('id', $customer)->value('name');
        
        return response()->json($data);


    }

    

    public function update(Request $request)
    {

        $id =  $action = $request->get('id');




        $type = Type::find($id);
        $type->name = $request->input('name');
        $type->description  = $request->input('description');
        $type->value  = $request->input('value');
        $type->level  = $request->input('level') ;
        $type->parentKey  = $request->input('parentKey');
        $type->groupType  = $request->input('groupType');
        $type->topValue =$request->input('topValue') ?? 0;
        $type->childType  = $request->input('childType');
        $type->userId  = $request->input('userId');
        $type->start_time  = $request->input('start_time');
        $type->end_time  = $request->input('end_time');
        $type->label  = $request->input('lable')?? 0;
        $type->save();

        $data = $type->id;

        return response()->json($data);


    }


    public function clon(Request $request){

       

        $grouptype = $request->input('productId');

        $type  = Type::select('*')->where('groupType',$grouptype)->get();

        $response['data'] = $type ;
   
        return response()->json($response);
    }

}
