<?php

namespace App\Http\Controllers;


use App\Models\Jobcarditem;
use App\Models\JobCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Auth;

class Deletecontroller extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    
    public function index()
    {

      $data['jobcarditems'] = Jobcarditem::orderBy('id','asc')->paginate(50);
      return view('jobcarditems.index', $data);
    }
    
    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
    return view('jobcarditems.create');
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
    'name' => 'required',
    'email' => 'required',
    'address' => 'required'
    ]);
    $jobcarditem = new Jobcarditem;
    $jobcarditem->save();

 
    Log::info("Hello saving jobcard item......");

      return redirect()->route('jobcarditems.index')->with('success','Jobcarditem has been created successfully.');
    }
    
    /**
    * Display the specified resource.
    *
    * @param  \App\jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function show(Jobcarditem $jobcarditem)
    {
    return view('jobcarditems.show',compact('jobcarditem'));
    } 
    
    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\Jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function edit(Jobcarditem $jobcarditem)
    {

    return view('jobcarditems.edit',compact('jobcarditem'));
    }
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
    $request->validate([
 
    ]);
    $jobcarditem = Jobcarditem::find($id);
    $jobcarditem->name = $request->name;
    $jobcarditem->jobCardId = $request->jobCardId;
    $jobcarditem->processId = $request->processId;
    $jobcarditem->qnt = $request->qnt;
    $jobcarditem->qntId = $request->qntId;
    $jobcarditem->save();

    return redirect()->route('job_cards.edit',$jobcarditem->jobCardId)
    ->with('success','Jobcarditem Has Been updated successfully');
    }

   /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function delete(Jobcarditem $jobcarditem)
    {
    $jobcarditem->delete();
    
    return redirect()->route('job_cards.edit',$jobcarditem->jobCardId)
    ->with('success','Jobcarditem has been deleted successfully');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Jobcarditem  $jobcarditem
    * @return \Illuminate\Http\Response
    */
    public function destroy(Jobcarditem $jobcarditem)
    {
        $jobcarditem->delete();
        return redirect()->route('job_cards.edit',$jobcarditem->jobCardId)
        ->with('success','Jobcarditem has been deleted successfully');
    }

    
 
}