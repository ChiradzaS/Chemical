<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FomularType ;
use Auth;

class FormulaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['formulas'] = FomularType::orderBy('id','asc')->paginate(100);
        return view('formulas.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('formulas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $formula = new FomularType;
        $formula->name = $request->name;
        $formula->type = $request->type;
        $formula->userId = Auth::id();
        $formula->active = $request->active;
        $formula->save();

        return redirect()->route('formula.index')
        ->with('success','Company Has Been updated successfully');

    
     
        
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
        return view('formulas.edit',compact('formula'));
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
        $formula = FomularType::find($id);
        $formula->name = $request->name;
        $formula->type = $request->type;
        $formula->active = $request->active;
        $formula->save();

        return redirect()->route('companies.index')
        ->with('success','Company Has Been updated successfully');
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
