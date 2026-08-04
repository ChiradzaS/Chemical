<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">


<!-- Script CDN -->


<script>
    .content {
      width: 100%;
      max-width: 100%;
}
</script>

<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');



    function createallocation(id) {

    
        const currentDate = new Date();

        const currentTime = currentDate.toLocaleTimeString();

       // alert("Current Time: " + currentTime);

        //alert( id);
        var selectedValues = {};
        var productid  = 120;
        var machine  = document.getElementById('machineId1-'+id).value;
        var shift  = document.getElementById('shiftId1-'+id).value;
        var operator  = document.getElementById('operator1-'+id).value;
        // var startTime  = document.getElementById('startTime1-'+id).value;
        // var endTime  = document.getElementById('endTime1-'+id).value;
        var startTime  = currentTime;
        var endTime  = currentTime;
        var qnt = document.getElementById("qnt1-"+id).value;
        var unit = document.getElementById("unitId1-"+id).value;
        var process = document.getElementById("processId1-"+id).value;
        var jobcarditem = id;

        if( machine == 0 ){
            alert('Please select machine.... ');
            return;
        }

        if(  operator == 0 ){
            alert('Please choose operator... ');
            return;
        }

        if(  shift == 0 ){
            alert('Please select shift... ');
            return;
        }

        if(  startTime == 0 ){
            alert('Please choose the start time ... ');
            return;
        }

        if(  endTime == 0 ){
            alert('Please choose the end time ... ');
            return;
        }
       

        





 

      
    //   var productid  = 120;
    //    var qnt = document.getElementById("qnt1").value;
    //    var startTime = document.getElementById("startTime1").value;
    //    var endTime = document.getElementById("endTime1").value;
    //    var shift = document.getElementById("shiftId1").value;
    //    var process = document.getElementById("processId1").value;
    //    var operator = document.getElementById("operator1").value;
    //    var machine = document.getElementById("machineId1").value;
    //    var unit = document.getElementById("unitId1").value;
    //    var qnt = document.getElementById("qnt1").value;
    //    var jobcarditem = document.getElementById("tdValue1").value;
       
    

       
    

        if( jobcarditem > 0){
        
        $.ajax({
        url: "{{ route('generateAllocation') }}",
        type: 'post',
        data: {_token: CSRF_TOKEN, productid: productid, jobcarditem:jobcarditem,qnt: qnt, startTime:startTime,endTime:endTime,shift:shift, process:process ,operator:operator,machine:machine,unit :unit,qnt:qnt  },
        dataType: 'json',
        success: function(response){
        
        var button = document.getElementById('myButton-'+id);
                     button.textContent = "Update";
                     button.disabled = true;
                     button.style.backgroundColor = '#778899';
                     
                    

        alert('Job card allocated to machine successfully');

         }

           })
        }
       
      
}

function updateAllocation(id){

        var productid  = 120;

        var machine  = document.getElementById('machineId-'+id).value;
       
        var shift  = document.getElementById('shiftId-'+id).value;
        
        var operator  = document.getElementById('operator-'+id).value;

      
        
        var startTime  = document.getElementById('startTime-'+id).value;
        
        var endTime  = document.getElementById('endTime-'+id).value;
       
        var allocation  = document.getElementById('allocation-'+id).value;
        
        var jobcarditem = id;

         

        if( jobcarditem > 0){

        $.ajax({
        url: "{{ route('updateAllocation') }}",
        type: 'post',
        data: {_token: CSRF_TOKEN, productid: productid,machine :machine, shift:shift ,operator :operator ,startTime: startTime,endTime:endTime,jobcarditem :jobcarditem,allocation:allocation },
        dataType: 'json',
        success: function(response){

        alert('Allocation Updated Successfully');

         }

           })
        }



}

</script>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
<meta charset="UTF-8">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >
<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script src="{{ asset('public/js/script.js') }}" ></script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<br>

</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Allocation per jobcard</th>



</tr>
</thead>
</table>
<br>
<br>




<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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


<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}


 
    h3{
        text-align: center;
    }
 </style>
<br>
</table>

<table  class="table table-borderless" >
<thead class="thead-dark">
<tr>
<th >Customer</th>
<th >Product</th>
<th >Qnt</th> 

<th >Pack</th>

<th ></th>
<th ></th>


<th >Machine Allocation</th>
<th ></th>


<th>Action</th>


</tr>
</thead>
@foreach ($job_cards as $job_card)
@php $tmpProduct = $porducts[$job_card->productId]; @endphp
@php $tmpUnittype = $unittypes[$job_card->unitId]; @endphp
@php $tmpCustomer = $customers[$job_card->customerId]; @endphp
@php $tmpstatus = $statustypes[$job_card->stateId]; @endphp
@php  $jobcarditems = App\Models\Jobcarditem ::where('jobCardId',$job_card->id )->distinct('processId')->get();


@endphp








<style>
    .highlight {
  background-color: #E7E8D1;
  }

  .highlights {
  background-color:  #A7BEAE;
}
</style>



<tr>
<td class="highlights"><strong>{{ $tmpCustomer->name }}</strong></td>
<td  class="highlights"><strong>{{ $tmpProduct->name }}</strong></td>
<td class="highlights"><strong>{{ $job_card->qnt }}</strong></td>
<td class="highlights"><strong>{{ $tmpUnittype->name}}</strong></td>
<td  class="highlights"></td>

<td  class="highlights"><strong>Machine</strong></td>
<td  class="highlights"><strong>Operator</strong></td>
<td  class="highlights"><strong>Shift</strong></td>



<td  class="highlights"></td>


</tr>







<form action="{{ route('allocation.store') }}" method="POST" enctype="multipart/form-data">
@csrf

@foreach ($jobcarditems as  $jobcarditem)
@php $tmpProduct = $porducts[$jobcarditem->productId]; @endphp
@php $tmpUnittype = $unittypes[$jobcarditem->unitId]; @endphp
@php $tmpProcesstype = $processtypes[$jobcarditem->processId]; @endphp
@php $tmpstatus = $statustypes[$jobcarditem->stateId]; @endphp

@php  $allocations = App\Models\Allocation::where('jobcarditemId',$jobcarditem->id)->get();   @endphp





@if(count($allocations)!=0)

<tr>





@foreach ($allocations as $allocation)

<input type="hidden" name="qnt" id="qnt-{{$jobcarditem->id}}" value="{{$jobcarditem->qnt}}">
<input type="hidden" name="processId" id="processId-{{$jobcarditem->id}}" value="{{$jobcarditem->processId}}">
<input type="hidden" name="unitId" id="unitId-{{$jobcarditem->id}}" value="{{$jobcarditem->unitId}}">
<input type="hidden" name="date" id="date-{{$jobcarditem->id}}" value="<?php echo date('Y-m-d'); ?>">
<input type="hidden" name="{{ $jobcarditem->id }}" id="{{ $jobcarditem->id }}" value="{{ $jobcarditem->id }}">
<input type="hidden" name="{{ $allocation->id }}" id="allocation-{{ $jobcarditem->id }}" value="{{ $allocation->id }}">







<td class="highlight"></td>
<td class="highlight">{{$tmpProduct ->name}}</td>
<td  class="highlight">{{$jobcarditem->outstanding}}</td>
<td class="highlight">{{$tmpUnittype->name}}</td>

<td class="highlight"></td>
<td  class="highlight">
   


<select  id="machineId-{{ $jobcarditem->id }}" name="machineId"    >
@foreach($machinetypes as $machinetype)
<option value="{{ $machinetype->id}} " @if ( $machinetype->id==$allocation->machineId) selected @endif >{{ $machinetype->name }} </option>
@endforeach
</select>
</td>

<td  class="highlight"> 
   <select   name="operator" id="operator-{{ $jobcarditem->id }}"  >
   @foreach($users as $user)
   <option value="{{ $user->id }}"  @if ( $user->id==$allocation->operator ) selected @endif >{{ $user->name }} </option>
   @endforeach
   </select></td>



<td  class="highlight">
    <select  id="shiftId-{{ $jobcarditem->id }}" name="shiftId"    >
@foreach($shifttypes as $shifttype)
<option value="{{ $shifttype->id }}" @if (  $shifttype->id==$allocation->shiftId ) selected @endif  >{{ $shifttype->name }} </option>
@endforeach
</select>

</td>






<td class="highlight">
    <button type="button" id="myButton-{{ $jobcarditem->id }}" onclick="updateAllocation('{{ $jobcarditem->id }}')" class="btn btn-secondary btn-sm">Update</button>&nbsp;</td>
<td>
</tr>

@endforeach

@else

<tr>






<input type="hidden" name="qnt" id="qnt1-{{$jobcarditem->id}}" value="{{$jobcarditem->qnt}}">
<input type="hidden" name="processId1" id="processId1-{{$jobcarditem->id}}" value="{{$jobcarditem->processId}}">
<input type="hidden" name="unitId1" id="unitId1-{{$jobcarditem->id}}" value="{{$jobcarditem->unitId}}">
<input type="hidden" name="date" id="date-{{$jobcarditem->id}}" value="<?php echo date('Y-m-d'); ?>">
<input type="hidden" name="{{ $jobcarditem->id }}" id="{{ $jobcarditem->id }}" value="{{ $jobcarditem->id }}">






<td  class="highlight"></td>
<td  class="highlight">{{$tmpProduct ->name}}</td>
<td  class="highlight">{{$jobcarditem->outstanding}}</td>
<td  class="highlight">{{$tmpUnittype->name}}</td>

<td  class="highlight"></td>
<td  class="highlight">
   

<select  id="machineId1-{{ $jobcarditem->id }}" name="machineId1"    >
<option value="0" disabled selected hidden>--select--</option>
@foreach($machinetypes as $machinetype)
<option value="{{ $machinetype->id}} " >{{ $machinetype->name }} </option>
@endforeach
</select>
</td>

<td  class="highlight"> 
   <select   name="operator" id="operator1-{{ $jobcarditem->id }}"  >
   <option value="0" disabled selected hidden>--select--</option>
   @foreach($users as $user)
   <option value="{{ $user->id }}"  >{{ $user->name }} </option>
   @endforeach
   </select></td>



<td  class="highlight">
    <select  id="shiftId1-{{ $jobcarditem->id }}" name="shiftId1"    >
    <option value="0" disabled selected hidden>--select--</option>
@foreach($shifttypes as $shifttype)
<option value="{{ $shifttype->id }}"  >{{ $shifttype->name }} </option>
@endforeach
</select>

</td>








<td  class="highlight" >
    <button type="button" id="myButton-{{ $jobcarditem->id }}" onclick="createallocation('{{ $jobcarditem->id }}')" class="btn btn-secondary btn-sm">Allocate</button>&nbsp;</td>
<td>
</tr>

@endif

</form>


@endforeach

</tr>

@endforeach

</table>





</body>
</html>