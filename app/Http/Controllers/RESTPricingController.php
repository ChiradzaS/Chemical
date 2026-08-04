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

class RESTPricingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $response = SetPrice::orderBy('customerId','asc')->get();

       

        return response()->json($response);

        

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

            // Generate the price name based on dimensions and gusset
    $priceName = $this->generatePriceName([
        'width' => $request->input('width'),
        'length' => $request->input('length'),
        'actualMicron' => $request->input('actualMicron'),
        'gusset' => $request->input('gusset'),
        'colourId' => $request->input('colourId'),
        'customerId' => $request->input('customerId'),
        'actualMicron' => $request->input('actualMicron'),
        'micron' => $request->input('micron')
    ]);

    //Log::info($priceName);

       
                
        $price = new SetPrice;
        $price->name = $priceName;
        $price->customerId = $request->input('customerId');
        $price->width  = $request->input('width');
        $price->gusset  = $request->input('gusset');
        $price->totalWidth  = $request->input('totalWidth') ?? 'none';
        $price->length  = $request->input('length');
        $price->micron  = $request->input('micron');
        $price->actualMicron =$request->input('actualMicron');
        $price->material  = $request->input('material');
        $price->colourId  = $request->input('colourId')?? 'none';
        $price->bagType  = $request->input('bagType');
        $price->pricePerKg  = $request->input('pricePerKg') ?? '0.00';
        $price->pricePer1000 =  $request->input('pricePer1000');
        $price->price =  $request->input('price');
        $price->unitId =  $request->input('unitId');
        $price->price2 =  $request->input('price2');
        $price->save();

                
    
        $response = $price->id ;



        return response()->json($response);
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
    public function destroy(Request $request)
    {
        
         $item = $request->input('itemId');
        

        
            $itemId = $request->input('itemId');

   
            $item = SetPrice::find($itemId); 
            if ($item) {
                $item->delete();
                return response()->json(['message' => 'Item deleted successfully'], 200);
            }

    return response()->json(['message' => 'Item not found'], 404);
}

       

    
    
    

    
}
