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
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Edit Vehicle Details</th>
</tr>
</thead>
</table>
<br>
</div>
<div class="pull-right">
<a class="btn btn-outline-info" href="{{ route('vehicles.index') }}" enctype="multipart/form-data"> Back</a>
</div>
</div>
</div>
<br>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('vehicles.update',$vehicle->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Vehicle Type</strong>
<select  name="vehicleType" id="VehicleType" class="form-control form-control-sm"  >
@foreach($vtypes as $vtype)
<option value="{{ $vtype->id }}"  @if($vtype->id==$vehicle->vehicleType) selected @endif >{{ $vtype->name }} </option>
@endforeach
</select>
@error('VehicleType')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Registration No</strong>
<input type="text" name="registrationNo" id="registrationNo" value="{{ $vehicle->registrationNo }}" class="form-control form-control-sm" placeholder="">
@error('registrationNo')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Name</strong>
<input type="text" name="name" id="name" value="{{ $vehicle->name }}" class="form-control form-control-sm" placeholder="">
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Description</strong>
<input type="text" name="description"  id="description" value="{{ $vehicle->description }}" class="form-control form-control-sm" placeholder="">
@error('description')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>


<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Manufacturer</strong>
<input type="text" name="manufacturerOfVehicle" id="manufacturerOfVehicle" value="{{ $vehicle->manufacturerOfVehicle}}"  class="form-control form-control-sm" placeholder="">
@error('manufacturerOfVehicle')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<button type="submit" class="btn btn-outline-info">Submit</button>
</div>
</form>
</div>
</body>
</html>