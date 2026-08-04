<?php

namespace App\Http\Controllers;

use App\Models\Machinery;
use Illuminate\Http\Request;
use Auth;

class MachineryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['machineries'] = Machinery::orderBy('id','asc')->paginate(50);
        return view('machinery.index', $data);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('machinery.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request )
    {
        $request->validate([
        'name' => 'required',
        ]);


        $machinery = new Machinery;
        $machinery->name = $request->name;
        $machinery->refNo = $request->refNo;
        $machinery->description = $request->description;
        $machinery->serialNo = $request->serialNo;
        $machinery->machineryTypeId = $request->machineryTypeId;
        $machinery->addressOfMachine = $request->addressOfMachine;
        $machinery->other = $request->other;
        $machinery->bookValue = $request->bookValue;
        $machinery->realisticValue = $request->realisticValue;
        $machinery->startDate = $request->startDate;
        $machinery->endDate = $request->endDate;
        $machinery->manufactureOfMachine = $request->manufactureOfMachine;
        $machinery->emailAddressManufacturer = $request->emailAddressManufacturer;
        $machinery->websiteManufacturer = $request->websiteManufacturer;
        $machinery->contactPersonOfManufacture = $request->contactPersonOfManufacture;
        $machinery->contactDetailsOfManufacture = $request->contactDetailsOfManufacture;
        $machinery->addressOfManufacturer = $request->addressOfManufacturer;
        $machinery->processId = $request->processId;
        //$machinery->userId = 68;
        $machinery->save();


        return redirect()->route('machinery.index')->with('success','Machinery has been created successfully.');

        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Machinery  $machinery
     * @return \Illuminate\Http\Response
     */
    public function show(Machinery $machinery)
    {
        return view('machinery.show',compact('machinery'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Machinery  $machinery
     * @return \Illuminate\Http\Response
     */
    public function edit(Machinery $machinery)
    {
        return view('machinery.edit',compact('machinery'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Machinery  $machinery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $request->validate([
            'name' => 'required',
            ]);
            $machinery = Machinery::find($id);
            $machinery->name = $request->name;
            $machinery->refNo = $request->refNo;
            $machinery->description = $request->description;
            $machinery->serialNo = $request->serialNo;
            $machinery->machineryTypeId = $request->machineryTypeId;
            $machinery->addressOfMachine = $request->addressOfMachine;
            $machinery->other = $request->other;
            $machinery->bookValue = $request->bookValue;
            $machinery->realisticValue = $request->realisticValue;
            $machinery->startDate = $request->startDate;
            $machinery->endDate = $request->endDate;
            $machinery->manufactureOfMachine = $request->manufactureOfMachine;
            $machinery->emailAddressManufacturer = $request->emailAddressManufacturer;
            $machinery->websiteManufacturer = $request->websiteManufacturer;
            $machinery->contactPersonOfManufacture = $request->contactPersonOfManufacture;
            $machinery->contactDetailsOfManufacture = $request->contactDetailsOfManufacture;
            $machinery->addressOfManufacturer = $request->addressOfManufacturer;
            $machinery->processId = $request->processId;
            $machinery->save();
            return redirect()->route('machinery.index')
            ->with('success','Machinery Has Been updated successfully');
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Machinery  $machinery
     * @return \Illuminate\Http\Response
     */
    public function destroy(Machinery $machinery)
    {
        $machinery->delete();
        return redirect()->route('machinery.index')->with('success','Machinery has been deleted successfully');
        
    }
}
