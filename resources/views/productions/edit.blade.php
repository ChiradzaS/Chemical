<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

</head>
<body>
{{-- @include('view') --}}
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Edit Production </h2>
</div>
<div class="pull-right">
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('productions.update',$production['id']) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')


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
        width: 15% !important;
    }
</style>

<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}
</style>

<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}
</style>


<input type="text" name="productionId"   value="{{ $production['id'] }}"   placeholder="Reference" hidden>

<br>

<strong>Employee :</strong>
<select  id="userId" name="userId" class="js-example-basic-single">
@foreach($user as $user)
<option disabled  value="{{ $user->id }}" @if($user->id==$production->userId) selected @endif>{{ $user->name }} </option>
@endforeach
</select>
&nbsp;&nbsp; &nbsp;
<strong>Process:</strong>&nbsp;&nbsp;
    <select  id="processId" name="processId"  class="js-example-basic-single" >
@foreach($processtypes as $processtype)
<option disabled  value="{{ $processtype->id }}"  @if($processtype->id==$production->processId) selected @endif  diabled >{{ $processtype->name }} </option>
@endforeach
</select>



&nbsp;&nbsp; &nbsp;


<br>

<br>

<br>
<strong>Shift :</strong>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    <select  id="shiftId" name="shiftId"  class="js-example-basic-single" >
@foreach($shifttypes as $shifttype)
<option disabled value="{{ $shifttype->id }}" @if($shifttype->id==$production->shiftId) selected @endif   >{{ $shifttype->name }} </option>
@endforeach
</select>


&nbsp; &nbsp;
<strong>Machine:</strong>
<select  id="machineryId" name="machineryId"  class="js-example-basic-single" >
@foreach($machinetypes as $machinetype)
<option disabled  value="{{ $machinetype->id }}" @if($machinetype->id==$production->machineryId) selected @endif    >{{ $machinetype->name }} </option>
@endforeach
</select>


<br>


<input type="text" name="refNo"   value="{{ $production->refNo }}"   hidden>


<textarea name="other" rows="2" cols="25" hidden>
{{ $production->other }}
</textarea>



<input type="text" name="serialNo"  value="{{ $production->serialNo }}"hidden >





<input type="text" name="value"  value="{{ $production->value }}" hidden>


<input type="text" name="stateId"  value="{{ $production->stateId }}"  hidden>
<br>
<hr>
<button type="submit" class="btn btn-outline-info">Save</button>
<a class="btn btn-outline-info" href="{{ route('productionitems.create',['productionId' => $production->id ,'customer' =>  $production->customerId]) }}">Add</a> 
<a class="btn btn-outline-info" href="{{ route('productions.create',['stock'=> $production->id]) }}">Add by Stock</a>
<a class="btn btn-outline-info" href="{{ route('index.index',['productionId' => $production->id, 'state' => $production->stateId, 'other' => $production->other ,'prntReport' => 'PRODUCTION_BY_PRODUCTIONID']) }}">Print</a>
<a class="btn btn-outline-info" href="{{ route('productions.create', ['ProductionId' => $production->id, 'btrn' => 'audit']) }}" >Audit </a>
<button type="submit"  name="myButton" value="finish" id=""  class="btn btn-outline-info">Finished</button>
</form>


<hr>
<h3>Production items</h3>
<table class="table table-striped" >
    <tr>
    <th  scope="col"> Id No</th>
    <th  scope="col"> Date</th>
    <th  scope="col"> JobcarditemId</th>
    <th  scope="col"> Product</th>
    <th  scope="col"> Quantity</th>
    <th  scope="col"> Unit</th>
    <th  scope="col"> State</th>
    <th  scope="col"> Outstanding</th>
   
    <th  scope="col" width="300px"> Action</th>
    </tr>
    @foreach ($productionitems as $productionitem)
    @php $tmpUnittype = $unittypes[$productionitem->unitId]; @endphp
    @php $tmpstatus = $statustypes[$productionitem->stateId]; @endphp
    @php $tmpProduct = $porducts[$productionitem->productId]; @endphp
    <tr>
    <td>{{ $productionitem->id }}</td>
    <td>{{ $productionitem->prodDate }}</td>
    <td>{{ $productionitem->jobcarditemId }}</td>
    <td>{{ $tmpProduct->name }}</td>
    <td>{{ $productionitem->qnt }}</td>
    <td>{{$tmpUnittype->name }}</td>
    @if ($productionitem->outstanding <= 0)

    <td>Completed</td>
    @else
    <td>{{ $tmpstatus->name}}</td>
    @endif

    <td>{{ $productionitem->outstanding }}</td>
   
    <td>
        
    <form action="{{ route('productionitems.destroy',$productionitem->id) }}" method="Post" >
    <a class="btn btn-outline-info" href="{{ route('productionitems.show',$productionitem->id) }}" >&nbspView&nbsp </a> 
    <a class="btn btn-outline-info" href="{{ route('productionitems.edit',$productionitem->id) }}" >Update</a>
   
    @csrf
    @method('DELETE') 
    <button type="submit" class="btn btn-outline-info" id="delete" value="delete" onclick="return confirm('Are you sure you want to delete')"  >Delete</button>
    </form>

    {{-- <a class="btn btn-default" href="#" role="button" 
    @if($disabled) disabled='disabled' @endif >Link</a> --}}
   
    @endforeach
</td>
</body>
</html>