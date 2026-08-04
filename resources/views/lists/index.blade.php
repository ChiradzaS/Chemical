<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->


<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script>
   

   function preventFormSubmission()  {

      
     var machine = document.getElementById("machineId").value;
     var operator = document.getElementById("operator").value;
     var date = document.getElementById("date").value;
     var shiftId = document.getElementById("shiftId").value;
     var processId = document.getElementById("processId").value;

     
    if(machine.length < 1){
        
        alert('Please add machine allocated to the job');
        event.preventDefault();
    }

    
    if(operator .length < 1){
        
        alert('Please select who is the operator for the job');
        event.preventDefault();
    }

    
    if(date.length < 1){
        
        alert('Please enter date');
        event.preventDefault();
    }

    
    if(shiftId.length < 1){
        
        alert('Please select shift');
        event.preventDefault();
    }

    
    if(processId.length < 1){
        
        alert('Please select the process');
        event.preventDefault();
    }


     




  
}


</script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<br>
<br>
<style>
    .centered-heading {
  text-align: center;
}
</style>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Allocation list</th>



</tr>
</thead>
</table>
<br>

</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<form action="{{ route('lists.index')}}" method="GET">
<table class="table table-striped" >

<style>
    .table table-striped{
        width: 10%;
}
</style>



<td width="200px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    
    <strong>Search by Job card</strong>
  </div>&nbsp
  <input type="text" name="jobcard"  class="form-control form-control-sm" placeholder="Enter jobcard id">
</div>
</td>








<td width="200px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    
    <strong>Search by Jobcarditem</strong>
  </div>&nbsp
  <input type="text" name="jobcarditem" class="form-control form-control-sm" placeholder="Enter jobcard item id">
</div>
</td>


<td width="100px">
 <button type="submit" value="query" name="action" class="form-control  btn-dark" >Search</button>
</td>

</form>

<style>
   btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
}
</style>
<div>

</div>
<br>
<table class="table table-striped" >






<tr>

<th  scope="col">Jobcard item </th>
<th  scope="col">Product</th>
<th  scope="col">Unit</th>
<th  scope="col">Quantity</th>
<th  scope="col">Machine</th>
<th  scope="col">Date</th>
<th  scope="col">Operator</th>
<th  scope="col">Process</th>
<th  scope="col">Shift</th>
<th  scope="col">Start</th>
<th  scope="col">End</th>



</tr>




@foreach ($allocations as $allocation)

<tr>

@php $tmpShifttype = $shifttypes[$allocation->shiftId]; @endphp
@php $tmpProcesstype = $processtypes[$allocation->processId]; @endphp
@php $tmpMachinetype = $machinetypes[$allocation->machineId]; @endphp
@php $tmpemployee = $users[ $allocation->operator]; @endphp
@php $tmpunits = $unittypes[ $allocation->unitId]; @endphp
@php $product  = App\Models\Jobcarditem ::where('id',$allocation->jobcarditemId)->value('productId');  @endphp
@php $tmproduct = $porducts[$product ]; @endphp


<td>{{$allocation->jobcarditemId }}</td>
<td>{{$tmproduct->name }}</td>
<td>{{$tmpunits->name}}</td>
<td>{{$allocation->qnt}}</td>
<td>{{$tmpMachinetype->name}}</td>
<td>{{$allocation->created_at }}</td>
<td>{{$tmpemployee->name }}</td>
<td>{{$tmpProcesstype->name }}</td>
<td>{{$tmpShifttype->name}}</td>
<td>{{$allocation->startTime}}</td>
<td>{{$allocation->endTime}}</td>




</tr>

@endforeach

</body>
</html>