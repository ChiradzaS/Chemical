<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">






<!--------------------------------------------------------------------------------------------------------------------------->


<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">

<a class="btn btn-outline-primary" href="{{ route('vehicletrans.create') }}"> Create New</a>
<br>
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Vehicle Fuel Maintanace List</th>
</tr>
</thead>
</table>



</div>
<div class="pull-right mb-2">
</div>
</div>
</div>

<table class="table table-striped" >
<tr>
<th  scope="col">Date</th>
<th  scope="col">Vehicle</th>
<th  scope="col"> Fuel-Type</th>
<th  scope="col"> Pump</th>
<th  scope="col"> Driver</th>
<th  scope="col"> Kms</th>
<th  scope="col"> No of litres</th>
<th  scope="col"> Kms per litre</th>
<th  scope="col" width="200px"> Action</th>
</tr>
@foreach ($trans as $tran)
@php $tmpPump = $pumps[$tran->pumpId]; @endphp
@php $tmpDriver = $drivers[$tran->driverId]; @endphp
@php $tmpFuel = $fuels[$tran->fuelId]; @endphp
@php $tmpvehicle = $vehicles[$tran->vehicleId]; @endphp

<tr>
<td>{{ $tran->created_at }}</td>
<td>{{ $tmpvehicle->name }}</td>
<td>{{ $tmpFuel->name }}</td>
<td>{{ $tmpPump->name }}</td>
<td>{{ $tmpDriver->name }}</td> 
<td>{{ $tran->vehicleKm }} km</td>
<td>{{ $tran->litres }} l</td>
<td>{{ $tran->litersPerKm }}  l/km</td>
<td>

<form action="{{ route('vehicletrans.destroy', $tran->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('vehicletrans.edit',$tran->id) }}">View</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete')">Delete</button>

</form>
</td>
</tr>
@endforeach
</table>
{!! $trans->links() !!}
</body>
</html>