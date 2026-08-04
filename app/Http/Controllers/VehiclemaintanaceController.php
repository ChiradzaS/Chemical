<?php

namespace App\Http\Controllers;

use App\Models\VehicleMaintanance;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehiclemaintanaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['vehicles'] = Vehicle::orderBy('created_at','asc')->paginate(50);
        return view('vehiclemaintanances.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

       $vehicleId =  $request->vehicle;
        
        return view('vehiclemaintanances.create')->with('vehicleId', $vehicleId);
    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)

   
    {
       // dd('right here ');
        // $request->validate([

        //     'vehicleId' => 'required',
        //     'serviceDate' => 'required',
        //     'serviceType' => 'required',
        //     'vehicleKm' => 'required',
        //     'serviceDetails' => 'required',

        //     ]);
 
            $vehicle = new VehicleMaintanance;
            $vehicle->vehicleId = $request->vehicleId;
            $vehicle->serviceDate = $request->serviceDate;
            $vehicle->serviceType = $request->serviceType;
            $vehicle->vehicleKm = $request->vehicleKm;
            $vehicle->serviceDetails = $request->serviceDetails;
            $vehicle->save();

            return redirect()->route('vehiclemaintanances.index')->with('success','Vehicle has been created successfully.');
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VehicleMaintanance  $vehicleMaintanance
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleMaintanance $vehicleMaintanance)
    {
        return view('vehiclemaintanances.show',compact('vehicle'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VehicleMaintanance  $vehicleMaintanance
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleMaintanance $vehicleMaintanance,$id)
    {


        $data['history'] = VehicleMaintanance::where('vehicleId',$id) 
                                             ->get();


        return view('vehiclemaintanances.edit',compact('vehicleMaintanance'))->with($data);
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VehicleMaintanance  $vehicleMaintanance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleMaintanance $vehicleMaintanance)
    {
        $request->validate([
            'registrationNo' => 'required',
            'name' => 'required',
            'description' => 'required',
            'vehicleType' => 'required',
            'manufacturerOfVehicle' => 'required',
            ]);
      
            $vehicle = Vehicle::find($id);
            $vehicle->registrationNo = $request->registrationNo;
            $vehicle->name = $request->name;
            $vehicle->description = $request->description;
            $vehicle->vehicleType = $request->vehicleType;
            $vehicle->manufacturerOfVehicle = $request->manufacturerOfVehicle;
            $vehicle->save();
            return redirect()->route('vehicles.index')
            ->with('success','Vehicle Has Been updated successfully');
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VehicleMaintanance  $vehicleMaintanance
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleMaintanance $vehicleMaintanance)
    {
        //
    }
}
