<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerPrice;
use App\Models\Order_item;
use App\Models\JobCard;
use App\Models\Type;
use App\Models\Porduct;
use App\Models\Allocation;
use App\Models\SetPrice;
use App\Models\Order;
use App\Models\set;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Brick\Math\BigDecimal;

class Pricing1Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $width = 0;
        $price = 0;
        $price2 = 0;
        $gusset = 0;
        $Twidth = 0;
        $length = 0;
        $micron = 0;
        $priceperkg = 0;
        $selectedMaterialTypeId = 0;
        $selectedColourTypeId=0;
        $selectedBagTypeId=0;
        $priceperproduct=0;
        $actualMicron=0;
        $selectedcustomerId=0; 
        $selectedunittypeId=0;   


        $url = env('APP_URL');
        
        

        $response = Http::get($url.'/qryprices/index');

        //Log::info('QQQQQQQQQQQQQQQQQQQQQQQ'.$response);
      
      
 
        if ($response->successful()) {


            
            

            $data['set_prices'] = json_decode($response, true);
            
            return view('pricings.index' , $data , [

                'width' => $width,
                'gusset' => $gusset,
                'Twidth' => $Twidth,
                'length' => $length,
                'micron' => $micron,
                'priceperkg' => $priceperkg,
                'selectedMaterialTypeId' => $selectedMaterialTypeId,
                'selectedColourTypeId' => $selectedColourTypeId,
                'selectedBagTypeId' => $selectedBagTypeId,
                'priceperproduct' => $priceperproduct,
                'actualMicron' => $actualMicron,
                'selectedcustomerId' => $selectedcustomerId,
                'selectedunittypeId' => $selectedunittypeId,
                 'price' => $price,
                 'price2' => $price2 

                // Merge the response data here
               
            ]);

        } else {
            dd('Sorry, there is an error with your request');
        }

        
        

       


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
   

        $url = env('APP_URL');
        
        

        $response = Http::get($url.'/qryprices/index');

       // Log::info('QQQQQQQQQQQQQQQQQQQQQQQ'.$response);
      
      
 
        if ($response->successful()) {


            
            

            $data['set_prices'] = json_decode($response, true);
            
            return view('pricings.index' , $data , [

                'width' => $width,
                'price' => $price ,
                'price2' => $price2,
                'gusset' => $gusset,
                'Twidth' => $Twidth,
                'length' => $length,
                'micron' => $micron,
                'priceperkg' => $priceperkg,
                'selectedMaterialTypeId' => $selectedMaterialTypeId,
                'selectedColourTypeId' => $selectedColourTypeId,
                'selectedBagTypeId' => $selectedBagTypeId,
                'priceperproduct' => $priceperproduct,
                'actualMicron' => $actualMicron,
                'selectedcustomerId' => $selectedcustomerId,
                'selectedunittypeId' => $selectedunittypeId,
                'price2' => $price2


                // Merge the response data here
               
            ]);

        } else {
            dd('Sorry, there is an error with your request');
        }
        

        return view('pricings.create', ['width' => $width,'gusset' => $gusset,'Twidth' => $Twidth,'length' => $length,'micron' => $micron,'priceperkg'=>$priceperkg,'selectedMaterialTypeId'=>$selectedMaterialTypeId,'selectedColourTypeId'=>$selectedColourTypeId,'selectedBagTypeId'=>$selectedBagTypeId ,'priceperproduct'=>$priceperproduct,'actualMicron'=> $actualMicron,'selectedcustomerId'=> $selectedcustomerId, 'selectedunittypeId' => $selectedunittypeId,'price' => $price ,'price2' => $price2],$data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        $request->validate([

            'unitId'      => 'required',
            'width'      => 'required',
            'length'     => 'required',
            'gusset'     => 'required',
            'micron'     => 'required',
            'price'      => 'required',
            'priceperkg' => 'required'
        

            
            ]);


          $value = $request->get('create');
        
    //     if(   $value){


    //    $product = new CustomerPrice;
    //    $product->userId = auth()->user()->id;
    //    $product->price = $request->priceperkg;
    //    $product->Twidth =  $request->Twidth;
    //    $product->gusset =  $request->gusset;
    //    if( $request->gusset == null){

    //     $gusset = 0;

    //     }
    //    $product->width = $request->width;
    //    $product->length  =  $request->length;
    //    $product->micron = $request->micron;
    //    $product->material = $request->materialType;
    //    $product->colour  =  $request->colour;
    //    $product->bagType  =  $request->bagType;
    //    $product->save();


    //     return redirect()->route('pricings.index');

           

    //     }
      
       





          
            $material =  $request->materialType;
            $selectedunittypeId = $request->unitId;

            $unitvalue = Type::where('grouptype', 'unit')
                               ->where('id',  $selectedunittypeId)
                               ->value('value');


           
            

      


            $virgin_constant = Type::where('grouptype', 'constant')
                                    ->where('name', 'virgin_constant')
                                    ->value('value');

            $recycled_constant = Type::where('grouptype', 'constant')
                                        ->where('name', 'recycled_constant')
                                        ->value('value');

     


             
            $materials = Type::where('grouptype', 'material')
                            ->where('description', 'virgin')
                            ->where('id',  $material)
                            ->value('id');
           


            if (empty($materials)) {

                $const=    $recycled_constant ;

            } else {

                $const =  $virgin_constant  ;

            }
            
            $constantvalue = $const ;

            $totalWidth = $request->Twidth;
            $length = $request->length;
            $micron = $request->micron;
            $priceperkg = $request->priceperkg;
            $actualMicron = $request->actualMicron;

            if($micron){

                $weightper1000 = (( ( (($totalWidth/10) * ($length/10) * ($micron/1000) ) )/$constantvalue));


                $priceperproduct =  $weightper1000;
                $price = $request->price;
    
                $weightperkg = ($weightper1000 / 1000) * $unitvalue;
    
                $priceperkg = $price / $weightperkg;
            }

            if($actualMicron){

                $weightper1000 = (( ( (($totalWidth/10) * ($length/10) * ($actualMicron/1000) ) )/$constantvalue));


                $priceperproduct =  $weightper1000;
                $price = $request->price;
    
                $weightperkg = ($weightper1000 / 1000) * $unitvalue;
    
                $priceperkg2 = $price / $weightperkg;

            }else{

                $weightper1000 = (( ( (($totalWidth/10) * ($length/10) * ($micron/1000) ) )/$constantvalue));


                $priceperproduct =  $weightper1000;
                $price = $request->price;
    
                $weightperkg = ($weightper1000 / 1000) * $unitvalue;
    
                $priceperkg2 = $price / $weightperkg;



            }



            




           



        





        //$price = $request->price;
        $Twidth  = $request->Twidth;
        $gusset = $request->gusset;
        if( $request->gusset == null){

            $gusset = 0;

        }
        $width = $request->width;
        $length = $request->length;
        $micron = $request->micron;
       

        //Log::info($actualMicron);


        $selectedMaterialTypeId = $request->materialType;

        $selectedColourTypeId = $request->colour;
        $selectedBagTypeId = $request->bagType;
        $selectedcustomerId = $request->customer;
    
    
    
      


        //$priceperproduct  = 0.00;
        //$priceperkg  = 0.00;

        //$userId =  auth()->user()->id;

        
       
        $url = env('APP_URL');
        
        

        $response = Http::get($url.'/qryprices/index');

        
 
        if ($response->successful()) {


            
            

            $data['set_prices'] = json_decode($response, true);
            
            return view('pricings.index' , $data , [

                'width' => $width,
                'price' => $price,
                'gusset' => $gusset,
                'Twidth' => $Twidth,
                'length' => $length,
                'micron' => $micron,
                'priceperkg' => $priceperkg ,
                'selectedMaterialTypeId' => $selectedMaterialTypeId,
                'selectedColourTypeId' => $selectedColourTypeId,
                'selectedBagTypeId' => $selectedBagTypeId,
                'priceperproduct' => $priceperproduct,
                'actualMicron' => $actualMicron,
                'selectedcustomerId' => $selectedcustomerId,
                'selectedunittypeId' => $selectedunittypeId,
                'price2' => $priceperkg2

                // Merge the response data here
               
            ]);

        } else {
            dd('Sorry, there is an error with your request');
        }

      

       
        
        // return view('pricings.index', [

        //     'weightper1000' => $weightper1000,
        //     'priceperproduct' => $priceperproduct,
        //     'width' => $width,
        //     'Twidth' => $Twidth,
        //     'length' => $length,
        //     'priceperkg' => $price ,
        //     'gusset' => $gusset,
        //     'micron' => $micron,
        //     'actualMicron' => $actualMicron,
        //     'selectedBagTypeId' => $selectedBagTypeId,
        //     'selectedColourTypeId' =>  $selectedColourTypeId,
        //     'selectedMaterialTypeId' => $selectedMaterialTypeId,
        //     'selectedcustomerId'=> $selectedcustomerId,

           

        // ],$data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function show(Pricing $pricing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function edit(Pricing $pricing)
    {
        //
    }

    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pricing $pricing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pricing  $pricing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pricing $pricing)
    {
        //
    }


    

     public function getTypeValue(Request $request) {

        


                $typeId = $request->typeId;

                $value = DB::table('types')
                            ->where('id', $typeId)
                            ->value('value');


                $response = $value;

    
      return response()->json($response);



}

public function saveprice(Request $request){

    $data = [
   
 
        'customerId'   => $request->customerId,
        'width'        => $request->width,
        'gusset'       => $request->gusset,
        'totalWidth'   => $request->totalWidth,
        'length'       => $request->length,
        'micron'       => $request->micron,
        'actualMicron' => $request->actualMicron ?? $request->micron,
        'material'     => $request->material,
        'colourId'     => $request->colourId,
        'bagType'      => $request->bagType,
        'pricePerKg'   => $request->pricePerKg,
        'pricePer1000' => $request->pricePer1000,
        'price'        => $request->price,
        'unitId'       => $request->unitId,
        'price2'       => $request->price2

      
      ];


      //Log::info( $data );
      
      $url = env('APP_URL');
      
      
      
      
      $response = Http::get($url.'/qryprices/store', $data);
      //dd($response);
      
      
      if ($response->successful()) {
       
        //$priceId = $response->json($response); 
        return response()->json($response);
        
      } else {
        
        dd('Sorry , there an error with your request');
      
      }
}
public function deleteprice(Request $request){

    $data = [
   
 
        'itemId'   => $request->itemId 
      ];


      $item = $request->itemId ;


      
      
      $url = env('APP_URL');

     
      
      
      
      
      $response = Http::get($url.'/qryprices/destroy', $data);
     
      
      
      if ($response->successful()) {
      
        return response()->json($response);
        
      } else {
        
        dd('Sorry , there an error with your request');
      
      }
}
}
