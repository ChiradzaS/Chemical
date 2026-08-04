<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">


<!-- Script CDN -->





<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script src="{{ asset('public/js/script.js') }}" ></script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
  <br>
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success! </strong>{{ $message }}
</div>
@endif


@if ($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Failed!</strong> {{ $message }}
</div>
@endif


<script>
  function closeNotification() {
    var alertElement = document.querySelector('.alert');
    if (alertElement) {
        alertElement.style.display = 'none';

    }

    
}


</script>


<style>
  
    .alert.alert-success.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }

    .alert.alert-danger.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }
</style>


</div>
<div class="pull-right mb-2">
</div>
</div>
</div>

<!-- <a class="btn btn-dark " href="{{ route('index.index',['prntReport' =>'ALL_JOBCARDS','id'=>'$clocking->id']) }}">Print Jobcard List</a> -->



<script>




$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    span.select2.select2-container.select2-container--classic{
        width: 70% !important;
    }
</style>

<div>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Clocking List</th>
</tr>
</thead>
</table>


<br>

<a class="btn btn-primary btn-lg" href="{{ route('schedules.index', ['refresh' => 'true']) }}">Refresh list </a>
<a class="btn btn-dark btn-lg" href="{{ route('index.index', ['prntReport' => 'ClockingList']) }}">Print clocking .pdf</a>
</div>


<br>



<table class="table table-striped">
  
    <tbody>
        @php
            $previousOperator = null;
        @endphp

        @foreach ($clockings as $clocking)

        <style>
    
    .light {
        background-color: #5F9EA0; /* Set the background color (e.g., red) */
        color: #FFFFFF; /* Set the text color (e.g., white) */
        width: 2000px; /* Set the desired width (e.g., 200px) */
    }



</style>

        @if ($clocking->name !== $previousOperator)
                       

        <thead class="light">
        <tr>
       
            <th scope="col">Name</th>
            <th scope="col">Date</th>
            <th scope="col" >Clock In - Out</th>
            <th scope="col" >Shift</th>
            <th scope="col" >Job Description</th>
            <th scope="col" >Hours per day</th>
            <th scope="col" >Clock in Time</th>
            <th scope="col" >Clock out TIME</th>
            <th scope="col" >Arrival</th>
            <th scope="col" >Depature</th>
            <th scope="col" >Total Days {{ $count = App\Models\Clocking::whereNotNull('clockInTime')->whereNotNull('clockOutTime')->where('name',$clocking->name)->count() }}</th>
            <th scope="col" ></th>
        </tr>
    </thead>

    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong>Valid Days</strong></td>
        <td ><strong>{{ $count = App\Models\Clocking::whereNotNull('clockInTime')->whereNotNull('clockOutTime')->where('name',$clocking->name)->count() }}</strong></td>
        <td></td>
    </tr>
                    @endif
            <tr  id="{{$clocking->id_class}}" >

            <td  id="{{ $clocking->id }}"  style="display: none;" >{{ $clocking->id }}</td>

                <td>{{ $clocking->name }}</td>
                <td>{{  date('D M j', strtotime($clocking->date))}}</td>
                <td  style="display: none;" >{{ $clocking->day }}</td>
                <td  style="display: none;" id="{{ $clocking->id }}In">{{  $clocking->clockInTime }}</td>
                <td  style="display: none;" id="{{ $clocking->id }}_Out">{{ $clocking->clockOutTime  }}</td>
                <td>{{ $clocking->clockInTime }} - {{ $clocking->clockOutTime }}</td>
                <td>{{ $clocking->shift }}</td>
   


               @php 

               $trimmedName = trim($clocking->name);

              

       
               $job_description = App\Models\UserDetails::where('name',$trimmedName)->value('userPosition');  
  
               $schedules = App\Models\Job_Schedule::where('job_description',$job_description)->where('day',$clocking->day)->first('hours');  

               $jobname =  intval($job_description );
    
             

                @endphp

               



                @if ($job_description)

               
                <td>{{  $name = App\Models\Type::where('id',$job_description)->value('name') }}</td>
             

                @else
                <td>--</td>
                @endif

               
               
               
             
                <td id="response1_{{ $clocking->id }}" >--</td>
                <td id="response2_{{ $clocking->id }}" >--</td>
                <td id="response3_{{ $clocking->id }}" >--</td>



           
       
       <td  id="arrival_{{ $clocking->id }}" >--</td>
       <td  id="depature_{{ $clocking->id }}"  >--</td>


        @php
            $confirmedCount = 0; // Initialize the confirmed count variable
        @endphp

       @if ($clocking->clockOutTime == null || $clocking->clockInTime == null) 
            <td id="unconfirmed_{{ $clocking->name }}" class="unconfirmed-status">0</td>
        @else 
            <td id="confirmed_{{ $clocking->name }}" class="confirmed-status">1</td>   
        @endif

       
    <td id="fullday_{{ $clocking->id }}"></td>

            </tr>
  
             
 


          
         
           
      
             
            

            @php
                $previousOperator = $clocking->name;
            @endphp



        @endforeach

        <script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    document.addEventListener("DOMContentLoaded", function() {
        const tdElements = document.querySelectorAll("td");

        tdElements.forEach(function(td) {
            const id = td.getAttribute("id");
            
            if (id !== null) {

                

            $.ajax({
            url: "{{ route('setschedule') }}",
            type: 'post',
            data: {_token: CSRF_TOKEN, id:id  },
            dataType: 'json',
            success: function(response){
                ///alert('one more time'+response);
               // alert(JSON.stringify(response));

                ///td.textContent = response['response1'];
                ///td.textContent = response['response2'];

       //alert('hoyooooo ' + response['response1']);
       //alert('hoyooooo ' + response['response2']);
       //alert('hoyooooo ' + response['response3']);


   

        document.getElementById('response1_'+id).textContent = response['response1'];
        document.getElementById('response2_'+id).textContent = response['response2'].substring(0, 5);
        document.getElementById('response3_'+id).textContent = response['response3'].substring(0, 5);


        var time_variable_1 = document.getElementById(id + 'In').textContent;
        // alert(time_variable_1);
        var time_variable_2 = document.getElementById('response2_' + id).textContent = response['response2'].substring(0, 5);
        // alert(time_variable_2);

        //var diff = getTimeDiff(time_variable_2, time_variable_1, 'm');

        //alert(diff);



        const valuesArray = [];


        
        const arrivalTimeStr = document.getElementById(id + 'In').textContent;
        const depatureTimeStr = document.getElementById(id+'_Out').textContent;


        if(arrivalTimeStr&&depatureTimeStr ){
        const clockInTimeStr = document.getElementById('response2_' + id).textContent = response['response2'].substring(0, 5);

        
        const arrivalTimeParts = arrivalTimeStr.split(":");
        const clockInTimeParts = clockInTimeStr.split(":");

        
        const arrivalHour = parseInt(arrivalTimeParts[0]);
        const arrivalMinute = parseInt(arrivalTimeParts[1]);
        const clockInHour = parseInt(clockInTimeParts[0]);
        const clockInMinute = parseInt(clockInTimeParts[1]);

     
        const minutesDifference = (arrivalHour * 60 + arrivalMinute) - (clockInHour * 60 + clockInMinute);

        //alert('WOOOOOZA1'.minutesDifference);


        
        if (minutesDifference > 0) {
        
        document.getElementById('arrival_'+id).textContent = ` ${minutesDifference} minutes late.`;
        document.getElementById('arrival_'+id).style.color = 'red';


        valuesArray.push(Math.abs(minutesDifference));

      
  


   

        
      
        } else if (minutesDifference < 0) {
       
        document.getElementById('arrival_'+id).textContent = `${Math.abs(minutesDifference)} minutes early.`;
        document.getElementById('arrival_'+id).style.color = 'green';

        // var dat = document.getElementById('arrival_' + id).textContent;
    
        

        } else {
       
        document.getElementById('arrival_'+id).textContent = "0 minutes";
        document.getElementById('arrival_'+id).style.color = 'green';
        
        }


        

      
       }else{

document.getElementById('arrival_'+id).textContent = "--";

}



      
 
       if(arrivalTimeStr&&depatureTimeStr ){
         


        


        

        const clockOutTimeStr = document.getElementById('response3_' + id).textContent = response['response3'].substring(0, 5);

        
        const depatureTimeParts = depatureTimeStr.split(":");
        const clockOutTimeParts = clockOutTimeStr.split(":");

        
        const depatureHour = parseInt(depatureTimeParts [0]);
        const depatureMinute = parseInt(depatureTimeParts [1]);
        const clockOutHour = parseInt(clockOutTimeParts[0]);
        const clockOutMinute = parseInt(clockOutTimeParts[1]);

     
        const minutesdepatureDifference = ( depatureHour * 60 + depatureMinute) - (clockOutHour * 60 + clockOutMinute );

     

        
        if (minutesdepatureDifference > 0) {
    // alert(` ${minutesdepatureDifference} minutes late.`);
    document.getElementById('depature_'+id).textContent = ` ${minutesdepatureDifference} minutes late.`;
    document.getElementById('depature_'+id).style.color = 'green';
    
} else if (minutesdepatureDifference < 0) {
    // alert(`${Math.abs(minutesdepatureDifference)} minutes early.`);
    document.getElementById('depature_'+id).textContent = `${Math.abs(minutesdepatureDifference)} minutes early.`;
    document.getElementById('depature_'+id).style.color = 'red';


    valuesArray.push(Math.abs(minutesdepatureDifference));

    

  
} else {
    // alert("On time");
    document.getElementById('depature_'+id).textContent = "0 minutes";
    document.getElementById('depature_'+id).style.color = 'green';
}



    }else{

        document.getElementById('depature_'+id).textContent = "--";


    }




  
    const sum = valuesArray.reduce((accumulator, currentValue) => accumulator + currentValue, 0);
    //alert('wqwqwqwqwqwertt'+sum);
    if(sum > 240){
        document.getElementById('fullday_'+id).textContent= '1/2 day' ;
        document.getElementById('fullday_'+id).style.color = '#700021';
        document.getElementById(id+'_class').style.color = '#FFCC33';
        
    }
  


   


       
    
              
              

         }

           })
           
            }
        });
    });





</script>


    </tbody>
</table>


>


{!! $clockings->links() !!}
</body>
</html>