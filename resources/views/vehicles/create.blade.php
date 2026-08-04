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

<title>Add Company Form - Laravel 8 CRUD</title>






</head>
<body>
    <div>
{{-- @include('view') --}}
<br>
</div>
<div class="container mt-2">
<div class="row">
    <div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
    <br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th>Add New Vehicle</th>
</tr>
</thead>
</table>
<br>
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
<form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Vehicle Type</strong>
<select  name="vehicleType" id="VehicleType" class="form-control form-control-sm"  >
<option value="" disabled selected hidden>-- select vehicle Type --</option>
@foreach($vtypes as $vtype)
<option value="{{ $vtype->id }}"   >{{ $vtype->name }} </option>
@endforeach
</select>
@error('vehicleType')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>


<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Registration No</strong>
<input type="text" name="registrationNo" id="registrationNo"  class="form-control form-control-sm" placeholder="">
@error('registrationNo')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Name</strong>
<input type="text" name="name" id="name" class="form-control form-control-sm" placeholder="">
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Description</strong>
<input type="text" name="description"  id="description" class="form-control form-control-sm" placeholder="">
@error('description')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>


<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Manufacturer</strong>
<input type="text" name="manufacturerOfVehicle" id="manufacturerOfVehicle" class="form-control form-control-sm" placeholder="">
@error('manufacturerOfVehicle')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>


<br>
<button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Submit</button> 
</div>
</form>
</body>
</html>