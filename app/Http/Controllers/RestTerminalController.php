<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Terminal;
use App\Models\Type;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use DB;
use Auth;

class RestTerminalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $data =  Terminal::orderBy('created_at','desc')->get();   


        return response()->json($data);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        
        $terminal = New Terminal();
        $terminal->name = $request->input('userId');
        $terminal->description  = $request->input('terminal') ;
        $terminal->value  = $request->input('terminalIpAddress');
        $terminal->level  = $request->input('shiftId') ;
        $terminal->parentKey  = $request->input('date');
        $terminal->groupType  = $request->input('machineId');
        $terminal->topValue =$request->input('processId') ?? 0;
        $terminal->childType  = $request->input('jobCardId');
        $terminal->userId  = $request->input('packer');
        $terminal->userId  = $request->input('productionId');
        $terminal->save();

        $data = $terminal->id;

        return response()->json($data);
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
}
