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


<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">

<br>

<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th> Vehicle List </th>
</tr>
</thead>
</table>
<br>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<a class="btn btn-outline-primary" href="{{ route('vehicles.create') }}"> Create New</a>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>

<table class="table table-striped" >
<tr>
<th  scope="col"> Vehicle Name</th>
<th  scope="col"> Vehicle Type</th>
<th  scope="col"> Registration No</th>

<th  scope="col"> Description</th>
<th  scope="col"> Manufacturer</th>
<th  scope="col" width="200px"> Action</th>
</tr>
@foreach ($vehicles as $vehicle)
@php $tmpvehicleType = $vtypes[$vehicle->vehicleType]; @endphp
<tr>
<td>{{ $vehicle->name }}</td>
<td>{{ $tmpvehicleType->name }}</td>
<td>{{ $vehicle->registrationNo }}</td>

<td>{{ $vehicle->description }}</td>
<td>{{ $vehicle->manufacturerOfVehicle }}</td>
<td>
<form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('vehicles.edit',$vehicle->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete')">Delete</button>
</form>
</td>
</tr>
@endforeach
</table>
{!! $vehicles->links() !!}
</body>
</html>