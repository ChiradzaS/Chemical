<!DOCTYPE html>
<html>
<head>
  
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div>
</div>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>View Production item</h2>
</div>
<br>
<br>
<div class="pull-right">
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('productionitems.update',$productionitem->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
<table width="250px" class="table table-striped" >
<td width="250px" >
<strong>Production id:</strong>
<input name="productionId"    value="{{$productionitem->productionId}}"  readonly >
</td>
</table >

<table class="table table-striped" >
<td width="250px" >
<strong> Job Card item Id:</strong>
<input   name="jobcarditemId"   value="{{$productionitem->jobcarditemId}}"  readonly  > 
</td>

</table >


<table class="table table-striped" >
<td width="250px" >
<strong>Product:</strong>
<select  id="productId" name="productId" disabled  >
@foreach($porducts as $porduct)
<option  value="{{ $porduct->id }}" @if($porduct->id==$productionitem->productId) selected @endif >{{ $porduct->name }}</option>
@endforeach
</select><br>
</td>
</table > 

<table class="table table-striped" >
<td width="250px" >
<strong>Unit :</strong>
<select  id="unitId" name="unitId"  disabled >
@foreach($unittypes as $unittype)
<option  value="{{ $unittype->id }}"  @if($unittype->id==$productionitem->unitId) selected @endif >{{ $unittype->name }} </option>
@endforeach
</select>
</td>

</table >
<table class="table table-striped" >
<td width="250px" >
<strong> Enter Quantity:</strong>
<input   name="qnt"      value="{{$productionitem->qnt}}" readonly >
</td>
</table >

<table class="table table-striped" >
<td width="250px" >
<strong>Comment:</strong>
<input   name="other"     value="{{$productionitem->other}}" readonly  >

</td>

</table >

<table class="table table-striped" >
<td width="250px" >
<strong>Weight:</strong>
<input name="weight"    value="{{$productionitem->weight}}" readonly ><br>

</table >

</form>
</td>
</table>
<button class="btn btn-outline-info"  onclick="javascript:window.history.back();">Go Back</button>
</body>
</html>