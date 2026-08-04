<?php

namespace App\Http\Controllers;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['vehicles'] = Vehicle::orderBy('created_at','asc')->paginate(50);
        return view('vehicles.index',$data);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('vehicles.create');
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
            'registrationNo' => 'required',
            'name' => 'required',
            'description' => 'required',
            'vehicleType' => 'required',
            'manufacturerOfVehicle' => 'required',
            ]);
 
            $vehicle = new Vehicle;
            $vehicle->registrationNo = $request->registrationNo;
            $vehicle->name = $request->name;
            $vehicle->description = $request->description;
            $vehicle->vehicleType = $request->vehicleType;
            $vehicle->manufacturerOfVehicle = $request->manufacturerOfVehicle;
            $vehicle->save();

            return redirect()->route('vehicles.index')->with('success','Vehicle has been created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle)
    {
        return view('vehicles.show',compact('vehicle'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit',compact('vehicle'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
            
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')
        ->with('success','Vehicle has been deleted successfully');
    }
}
