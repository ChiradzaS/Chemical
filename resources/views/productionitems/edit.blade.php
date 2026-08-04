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
<h2>Update Production item</h2>
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
<form action="{{ route('productionitems.update',$productionitem->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

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
        width: 30% !important;
    }
</style>

<strong>Production id:</strong>
<input name="productionId"    value="{{$productionitem->productionId}}"  readonly >
<br>

<strong> Job Card item Id:</strong>
<input   name="jobcarditemId"   value="{{$productionitem->jobcarditemId}}"  readonly  > 

<strong>Product:</strong>
<select  id="productId" name="productId"  class="js-example-basic-single">
@foreach($porducts as $porduct)
<option  value="{{ $porduct->id }}" @if($porduct->id==$productionitem->productId) selected @endif >{{ $porduct->name }}</option>
@endforeach
</select><br>
  
   
<br>

<strong>Unit</strong>
<select  id="unitId" name="unitId"   class="js-example-basic-single">
@foreach($unittypes as $unittype)
<option  value="{{ $unittype->id }}"  @if($unittype->id==$productionitem->unitId) selected @endif >{{ $unittype->name }} </option>
@endforeach
</select>
<br>

<strong> Enter Quantity:</strong>
<input   name="qnt"      value="{{$productionitem->qnt}}" >

<br>

<strong>Comment:</strong>
<input   name="other"     value="{{$productionitem->other}}" >
<br>



<strong>Weight:</strong>
<input name="weight"    value="{{$productionitem->weight}}"><br>




<br>
<button type="submit"  name="myButton"  value="save" id="save" class="btn btn-outline-info">Save</button>

</form>
</body>
</html>