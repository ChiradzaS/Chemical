<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Productionitem;
use App\Models\User;
use App\Models\Machinery;
use App\Models\Production;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class BarcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //dd('HOYOOOOOOOOO');
        return view('barcodes.index');
        //
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
        //
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


    public function getScannedProductionid(Request $request)
    {
        
 
        $code =  $request->input('barcode');

        // Check if the unique_code exists in the Productionitem table
        $existingCode = Productionitem::where('unique_code', $code )->exists();

        if ($existingCode) {
            // Retrieve the roll_id for the given code
            $rollId = Productionitem::where('unique_code', $code )
                        ->value('rollId');
            
            if ($rollId) {
                // Use the roll_id to fetch user_id and machinery_id from the Production table
                $productionData = Production::where('id', $rollId)
                                ->select('userId', 'machineryId')
                                ->first();
                
                if ($productionData) {

                  
                    // Prepare the response array
                    $response = [
                        'userId' => User::where('id', $productionData->userId)->value('name'),
                        'machineId' => Machinery::where('id', $productionData->machineryId)->value('name')
                    ];
                    
                    return response()->json($response);
                }
            }else{
                $response = [
                    'userId' => 'Roll not scanned!!',
                    'machineId' => 'Roll not scanned!!'
                ];

                return response()->json($response);


            }
        }

        // Return a default response if no data is found
        return response()->json(['message' => 'No data found'], 404);

      
     
   
     
   
        
    }
   
}
