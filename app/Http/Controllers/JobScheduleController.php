<?php

namespace App\Http\Controllers;

use App\Models\Job_schedule;
use App\Models\Clocking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use DateTime;
use DB;


class JobScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        if ($request->has('refresh')) {

        Clocking::truncate();
        return view('importFile')->with('success', 'Now you can add upload a new list');


        }

        $data['schedules'] = Job_schedule::orderBy('created_at','asc')->paginate(100);
        return view('schedules.index',$data);


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('schedules.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
            if (Job_schedule::where('job_description', $request->job_description)
                            ->where('shift', $request->shift)
                            ->where('day', $request->day)
                            ->exists()) 

            {

            return redirect()->back()->with('error', 'This schedule already exsist in the system if you would like to make any changes please update it from the list.');

            }

        $schedule = new Job_schedule;
        $schedule->job_description = $request->job_description;
        $schedule->shift = $request->shift;
        $schedule->start_time = $request->start_time;
        $schedule->end_time = $request->end_time;


        $start_time = new DateTime($request->start_time);
        $end_time = new DateTime($request->end_time);
        $hours_difference = $start_time->diff($end_time)->h;
        $schedule->hours = $hours_difference;
        

        
        $schedule->day = $request->day;
        $schedule->save();
        
     
        return redirect()->route('schedules.create')->with('success','Schedule has been created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Job_schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show(Job_schedule $job_schedule)
    {
        return view('schedules.show',compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Job_schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Job_schedule $schedule)
    {
       

        $schedules =DB::table('job_schedules')->where('id',$schedule->id)->get();
        View::share('schedules', $schedules);

        //dd($schedules);

        return view('schedules.edit',compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Job_schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Job_schedule $schedule)
    {

        //dd(''.$schedule);

        $schedule =  Job_schedule::find($schedule->id);
        $schedule->job_description = $request->job_description;
        $schedule->shift = $request->shift;
        $schedule->start_time = $request->start_time;
        $schedule->end_time = $request->end_time;



        $start_time = new DateTime($request->start_time);
        $end_time = new DateTime($request->end_time);
        $hours_difference = $start_time->diff($end_time)->h;
        $schedule->hours = $hours_difference;
        

        
        $schedule->day = $request->day;
        $schedule->save();

        return redirect()->route('schedules.create')
        ->with('success','Job schedule Has Been updated successfully');
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Job_schedule  $Job_schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Job_schedule $Job_schedule)
    {
        //
    }

    public function setschedule(Request $request) {

        $id = $request->id;

        $response = $id ;

        $clocking = DB::table('clockings')->where('id', $id)->first();
        if ($clocking) {
            $clockingname = $clocking->name;
            $clockinday = trim($clocking->day);
            $shift = $clocking->shift;

            $clockingday = DB::table('types')->where('name', $clockinday)->value('id');


            $givenDateTime = DateTime::createFromFormat('H:i', $clocking->clockInTime );

               
            $dayShiftStart = DateTime::createFromFormat('H:i', '05:30');
            $dayShiftEnd = DateTime::createFromFormat('H:i', '18:00');

             
            


            if ( trim($shift) == 'day') {
                
                $clockinshiftTmp = DB::table('types')->where('name', 'day')->value('id');
            } else {
              
                $clockinshiftTmp = DB::table('types')->where('name', 'night')->value('id');
            }
            

            $jobdescription = DB::table('user_details')->where('name', $clockingname)->value('userPosition');



            $schedules = DB::table('job_schedules')->where('job_description',  $jobdescription)
                                                   ->where('shift',  $clockinshiftTmp)
                                                   ->where('day',  $clockingday)
                                                   ->get();

                                                  // Log::info('Apoooooooooo'.$schedules);



                                                   if ( $schedules ) {

                                                    //return response()->json($response1 = $schedules->hours,$response3 = $schedules->end_time,$response2 = $schedules->start_time );
                                                    
                                                   foreach($schedules as $schedule){


                                                   

                                                
                                                  

                                                    return response()->json([
                                                        'response1' =>  $schedule->hours,
                                                        'response3' =>  $schedule->end_time,
                                                        'response2' => $schedule->start_time
                                                        
                                                    ]);


                                                    
                                                   }


                                                    
                                                   }else{

                                                  
                                                   return response()->json([
                                                    'response1' => '--',
                                                    'response2' => '--',
                                                    'response3' => '--'
                                                ]);

                                                   }
            
        }
        //dd( $jobdescription);
        





        
        return response()->json([
            'response1' => '--',
            'response2' => '--',
            'response3' => '--'
        ]);
       


    }


}
