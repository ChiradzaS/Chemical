<?php

namespace App\Http\Controllers;

use App\Models\PumpTrans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use DB;

class PumpTransController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['trans'] = PumpTrans::orderBy('created_at','asc')->paginate(100);
        return view('vehicletrans.index',$data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('vehicletrans.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $currentLtrs = $request->current;

        $previousTrans = PumpTrans::where('vehicleId', $request->vehicleId)
                                    ->orderBy('created_at', 'desc')
                                    ->first();


        if($previousTrans){
            

        $kilometersUsed = $request->vehicleKm - $previousTrans->vehicleKm;
        $litersUsed = $previousTrans->litres;

        // if ( $currentLtrs > 0){

        //     $litersUsed =   $currentLtrs - $previousTrans->litres;

        // }
        
        if ($kilometersUsed > 0) {

            $litersPerKm = $litersUsed / $kilometersUsed * 100;

        } else {
          
            $litersPerKm = 0;
        }

    }else{

        $litersPerKm = 0 ;

    }
        

        

            $trans = new PumpTrans;
            $trans->vehicleKm = $request->vehicleKm;
            $trans->litres = $request->litres;
            $trans->pumpId = $request->pumpId;
            $trans->fuelId = $request->fuelId;
            $trans->driverId = $request->driverId;
            $trans->vehicleId = $request->vehicleId;
            $trans->litersPerKm = $litersPerKm;
            $trans->save();

              return redirect()->route('vehicletrans.index')->with('success','Pump transcation has been created successfully.');
            
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PumpTrans  $pumpTrans
     * @return \Illuminate\Http\Response
     */
    public function show(PumpTrans $pumpTrans)
    {
        return view('companies.show',compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PumpTrans  $pumpTrans
     * @return \Illuminate\Http\Response
     */
    public function edit(PumpTrans $Trans,$id)
    {
       // dd('////////\\\\\\\\\\\\\\\\'.$id);
        $trans = DB::table('pump_trans')->where('id',$id)->first();


      View::share('trans',$trans );

     // dd('QQQQ'.$trans);

        return view('vehicletrans.edit',compact('Trans'));
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PumpTrans  $pumpTrans
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PumpTrans $pumpTrans)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PumpTrans  $pumpTrans
     * @return \Illuminate\Http\Response
     */
    public function destroy(PumpTrans  $pumpTrans,$id)
    {
        ///dd('hhooooooooooooo'.$tran);

        $item = PumpTrans::findOrFail($id);
        $item->delete();
            return redirect()->route('vehicletrans.index')
            ->with('success','Transcation has been deleted successfully');
        
    }
}
