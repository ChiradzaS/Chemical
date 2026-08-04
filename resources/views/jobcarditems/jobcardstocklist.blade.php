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


<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
<meta charset="UTF-8">

<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script src="{{ asset('public/js/script.js') }}" ></script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
@if ($message = Session::get('success'))
<div class="alert alert-success">
<p>{{ $message }}</p>
</div>
@endif
@if(Session::has('message'))
    <div class="alert {{ Session::get('alert-class') }}">
        {{ Session::get('message') }}
    </div>
@endif

<h3></h3>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<br>
<br>
<br>
<form action="{{ route('jobcarditems.index')}}" method="GET">

<table class="table table-striped" >
<tr>
<td >
   <strong>Customer</strong>
  <select name="customerId" id="customerId"   class="js-example-basic-single">
  <option value="-9" >---- select customer ----</option>
    @foreach($customers as $customer)
    <option value="{{$customer->id}}" >{{ $customer->name }} </option>
    @endforeach
    </select>

</td>

<td>
    <strong>Product</strong>
  <select name="productId" id="productId"  class="js-example-basic-single" >
  <option value="-9" >------ select product ------</option>
    @foreach($porducts as $porduct)
<option value="{{ $porduct->id }}" >{{ $porduct->name }}</option>
@endforeach
    </select>

</td>
<td >
<strong>State</strong>
  <select name="stateId" id="stateId" placeholder="-- Select Customer --"  class="js-example-basic-single">
  <option value="-9" >--select-- </option>
    @foreach($statustypes as $state)
    <option value="{{$state->id}}" >{{ $state->name }} </option>
    @endforeach
    </select>
    </td>
<td  >
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">From</span>
  </div>
  <input type="date" name="fromDate" id="fromDate" class="form-control">
</div>
</td>
<td >
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1" >To</span>
  </div>
  <input type="date" name="toDate"  id="toDate" class="form-control">
</div>
</td>
<td  >
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Job Id</span>
  </div>
  <input type="text" name="jobcard" id="jobcard" class="form-control">
</div>
</td>
<td   ><button type="submit" value="query" name="action" class="form-control  btn-dark" >Search</button></td>
</tr>
</form>
</table>



<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>



<style>
    span.select2.select2-container.select2-container--classic{
        width: 70% !important;
    }
</style>



<br>
</table>

<table  class="table table-borderless" >
<thead class="thead-dark">
<tr>
<th ><strong> ALL JOB CARD LIST</strong></th>
<th ></th>
<th ></th>
<th ></th> 
<th ></th> 
<th ></th>
<th ></th>
<th ></th>
<th></th>



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
<td  class="highlights" width="30%"><strong>{{ $tmpCustomer->name }}</strong></td>


<td  class="highlights"></td>
<td  class="highlights"><strong>Product</strong></td>
<td  class="highlights"><strong>Qnt</strong></td>
<td  class="highlights"><strong>Outstanding</strong></td>
<td  class="highlights"><strong>Pack</strong></td>
<td class="highlights"><strong>{{$tmpstatus->name}}  {{ $job_card->DateComplete }}</strong></td>
<td  class="highlights"><strong>Process</strong></td>
<td  class="highlights"></td>






</tr>








@foreach ($jobcarditems as  $jobcarditem)
@php $tmpProduct = $porducts[$jobcarditem->productId]; @endphp
@php $tmpUnittype = $unittypes[$jobcarditem->unitId]; @endphp
@php $tmpProcesstype = $processtypes[$jobcarditem->processId]; @endphp
@php $tmpstatus = $statustypes[$jobcarditem->stateId]; @endphp

<tr>













<td  class="highlight">{{$jobcarditem->id}}</td>
<td  class="highlight"></td>
<td  class="highlight">{{$tmpProduct ->name}}</td>
<td  class="highlight">{{$jobcarditem->outstanding}}</td>
<td  class="highlight">{{$jobcarditem->qnt}}</td>
<td  class="highlight">{{$tmpUnittype->name}}</td>
<td class="highlight" width="11%">{{$tmpstatus->name}}</td>
<td  class="highlight"><strong>{{$tmpProcesstype ->name}}</strong></td>
<td  class="highlight" width="7%" ><a class="btn btn-secondary btn-sm" href="{{ route('index.create', ['jobCardId' => $jobcarditem->jobCardId, 'other' => $jobcarditem->other, 'state' => $jobcarditem->stateId,'jobcarditemId' => $jobcarditem->id ,'productId' => $jobcarditem->productId,'process' => $tmpProcesstype->name])  }}">&nbsp;&nbsp;Print&nbsp;&nbsp;</a></td>
















</tr>

@endforeach








@endforeach

</table>





</body>
</html>