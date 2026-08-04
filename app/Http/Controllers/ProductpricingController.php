<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Productpricing;
use App\Models\Porduct;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class ProductpricingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // $data['prices'] = Porduct::orderBy('updated_at','asc')->paginate(500);
        // return  view('prices.index ', $data,['state'=> 0,'productId'=> -9,'color' => -9,'bagType' => -9, 'materialTypeId' => -9 ]);


        $value = $request->input('productType');
        $action = $request->get('action');


        if ($value == 'finished-Product' || $action <> null && trim($action, ' ') == 'query' ) {

    
            $productId = $request->get('productId');
            $color = $request->get('color');
            $bagTypes = $request->get('bagType');
            $materialTypeId= $request->get('materialTypeId');
          
            $id = DB::table('types')
                    ->where('name' ,'=', $value)
                    ->value('id');
          
          
            
          $productComp = '<>';
          if ($productId > 0) {
             $productComp = '=';
          }
          
          
          
          $colorComp = '<>';
          if ($color > 0) {
             
               
               $colorComp = '=';
          }
          
          $bagTypeComp = '<>';
          if ($bagTypes > 0) {
            $bagTypeComp = '=';
          }
          
          
          $materialComp = '<>';
          if ($materialTypeId > 0) {
           
            $materialComp = '=';
          }        
          
            $data['prices'] = Porduct:: 
                                            where('id',''.$productComp,$productId)
                                          ->where('color', ''.$colorComp,$color)
                                          ->where('bagType',''.$bagTypeComp,$bagTypes)
                                          ->where('materialTypeId', ''.$materialComp,$materialTypeId)    
                                          ->where('productType',100)                               
                                          ->orderBy('id','desc')->paginate(500);
          
          
              return  view('prices.index', $data ,['state'=>0,'productId'=> -9,'color' => -9,'bagType' => -9, 'materialTypeId' => -9 ]);                               
           
            }
            

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $productId = $request->get('productId');

        //  echo "<pre>";
        //  print_r($productId);
        //  exit;
        //$data['prices'] = Productpricing::orderBy('updated_at','asc')->paginate(500);
        $prices = DB::table('productpricings')
                        ->where('productId', $productId)
                        ->orderBy('active','desc') 
                        ->get();

        $porduct = Porduct::find($productId);
        View::share('product',$porduct);
      
        return view('prices.create',['prices'=> $prices]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

       


        $pricing = new Productpricing;
        $pricing->productId = $request->productId;
        $pricing->formulaType = $request->formulaType;
        $pricing->customerType = $request->customerType;
        $pricing->unitId = $request->unitId;
        $pricing->price = $request->price;
        $pricing->active = $request->active;
        $pricing->userId = Auth::id();
        $pricing->save();

        return view('prices.index');

   
   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function pricecheck(Request $request)
    {
       
       $customer = $request->customer;
       $product = $request->product;
       $unit = $request->unit;
       $formula = $request->formula;
       $price = $request->price;

  

       


        $lastAddedPrice = DB::table('productpricings')
                            ->where('productId',$product)
                            ->where('customerType',$customer)
                            ->where('formulaType', $formula)
                            ->where('unitId', $unit)   
                            ->orderBy('created_at', 'desc')
                            ->select('id')
                            ->first();



        if(!$lastAddedPrice){

            $pricing = new Productpricing;
            $pricing->productId = $product;
            $pricing->formulaType = $formula;
            $pricing->customerType = $customer;
            $pricing->unitId = $request->unit;
            $pricing->price = $request->price;
            $pricing->userId = Auth::id();
            $pricing->active = 1;
            $pricing->save();


        }
      

      

        if($lastAddedPrice){

            $id = $lastAddedPrice->id;

        DB::table('productpricings')
            ->where('id', $id)
            ->update(['active' => 0]);

        
            $pricing = new Productpricing;
            $pricing->productId = $product;
            $pricing->formulaType = $formula;
            $pricing->customerType = $customer;
            $pricing->unitId = $request->unit;
            $pricing->price = $request->price;
            $pricing->userId = Auth::id();
            $pricing->active = 1;
            $pricing->save();

        }

            $response = $lastAddedPrice->id ;
            $response = 0;


       return response()->json($response);


    }
   
    
}
