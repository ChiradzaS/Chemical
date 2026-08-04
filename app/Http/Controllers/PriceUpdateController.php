<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\CustomerPrice;
use App\Models\Order_item;
use App\Models\JobCard;
use App\Models\Type;
use App\Models\Porduct;
use App\Models\SetPrice;
use App\Models\Oder;
use App\Models\set;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Brick\Math\BigDecimal;

class PriceUpdateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        $data['setprices'] = SetPrice::orderBy('created_at','asc')->get();
        return view('setprices.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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


        $setPrice = SetPrice::findOrFail($request->id);

        // Assign values from the request
        $Twidth = $request->Twidth;
        $gusset = $request->gusset ?? 0; // Default to 0 if gusset is null
        $width = $request->width;
        $length = $request->length;
        $micron = $request->micron;
    
        // Example calculations (replace with your actual logic)
        $totalWidth = $width + $gusset; // Example calculation for total width
        $priceperkg = 5.5; // Example value, replace with your logic
        $actualMicron = $micron; // Example value, replace with your logic
        $weightper1000 = 10; // Example value, replace with your logic
        $priceperproduct = 55; // Example value, replace with your logic
        $price = $request->price; // Assuming price comes from the request
        $weightperkg = 2; // Example value, replace with your logic
        $priceperkg2 = 5.5; // Example value, replace with your logic
    
        // Selected options from the request
        $selectedMaterialTypeId = $request->materialType;
        $selectedColourTypeId = $request->colour;
        $selectedBagTypeId = $request->bagType;
        $selectedcustomerId = $request->customer;
    



        
        return view('setprices.edit', ['id' => 1],compact(
            'setPrice',
            'totalWidth',
            'length',
            'micron',
            'priceperkg',
            'actualMicron',
            'weightper1000',
            'priceperproduct',
            'price',
            'weightperkg',
            'priceperkg2',
            'Twidth',
            'gusset',
            'width',
            'selectedMaterialTypeId',
            'selectedColourTypeId',
            'selectedBagTypeId',
            'selectedcustomerId'
        ));




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
    $setPrice = SetPrice::findOrFail($id);
   //dd($setPrice );
    return view('setprices.edit', compact('setPrice'));
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


        //Log::info('OOOOOOOQQQQQQQQQQQQQQQQQQQQQQ');


        $priceName = $this->generatePriceName([
            'width' => $request->width,
            'length' => $request->length,
            'actualMicron' => $request->actualMicron,
            'gusset' => $request->gusset,
            'colourId' => $request->colour,
            'customerId' => $request->customerId,
            'actualMicron' => $request->actualMicron,
            'micron' => $request->micron
        ]);


        $price = SetPrice::find($id);
        $price->name =  $priceName;
        $price->customerId = $request->customer;
        $price->width  = $request->width;
        $price->gusset  = $request->gusset;
        $price->totalWidth  = $request->Twidth;
        $price->length  = $request->length;
        $price->micron  = $request->micron;
        $price->actualMicron =$request->actualMicron;
        $price->material  = $request->materialType;
        $price->colourId  = $request->colour;
        $price->bagType  = $request->bagType;
        $price->pricePerKg  =  $request->pricePerKg;
        $price->pricePer1000 =  $request->priceperproduct;
        $price->price =  $request->price;
        $price->unitId =  $request->unitId;
        $price->price2 =  $request->price2;
        $price->save();




        return redirect()->route('setprices.index');



  
    }


    private function generatePriceName(array $data): string
    {
    
            // Get colour name if colour ID is provided
            $colourName = 'N/A';
            if ($data['colourId'] !== 'none') {
                // Assuming you have a Colour model
                $colour = Type::find($data['colourId']);
                if ($colour) {
                    $colourName = $colour->name;
                }
            }
    
    
    
        if ($data['gusset'] > 0) {
            return sprintf(
                '%d(%d + %d)mm x %dmm x %dmic %s %s %s',
                $data['width'],
                $data['gusset'] / 2,
                $data['gusset'] / 2,
                $data['length'],
                $data['actualMicron'],
                $data['micron'],
                $data['customerId'],
                $colourName
            );
        }
    
    
    
        return sprintf(
            '%dmm x %dmm x %dmic %s %s %s',
            $data['width'],
            $data['length'],
            $data['actualMicron'],
            $data['micron'],
            $data['customerId'],
            $colourName
        );
    
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


    public function getConstants(Request $request)
    {
        // Validate the request
        $request->validate([
            'unitId' => 'required|integer|exists:types,id',
            'materialType' => 'required|integer|exists:types,id',
        ]);

        // Fetch values from database
        $unitValue = Type::where('grouptype', 'unit')
            ->where('id', $request->unitId)
            ->value('value');

        $virginConstant = Type::where('grouptype', 'constant')
            ->where('name', 'virgin_constant')
            ->value('value');

        $recycledConstant = Type::where('grouptype', 'constant')
            ->where('name', 'recycled_constant')
            ->value('value');

        $isVirgin = Type::where('grouptype', 'material')
            ->where('description', 'virgin')
            ->where('id', $request->materialType)
            ->exists();

        return response()->json([
            'unitValue' => $unitValue,
            'constantValue' => $isVirgin ? $virginConstant : $recycledConstant,
        ]);
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

    
    public function getTypeValue(Request $request) {

        


        $typeId = $request->typeId;

        $value = DB::table('types')
                    ->where('id', $typeId)
                    ->value('value');


        $response = $value;


return response()->json($response);



}

}
