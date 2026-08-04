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
<th >Maintanance History</th>
</tr>
</thead>
</table>
<br>
</div>
<div class="pull-right">
</div>
</div>
</div>
<br>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif

<table class="table table-striped" >
<tr>
<th  scope="col">Vehicle Name</th>
<th  scope="col">Service Date</th>
<th  scope="col">Service Type</th>
<th  scope="col">Vehicle Milage</th>
<th  scope="col">Service Details</th>

</tr>
@foreach ($history as $histor)
@php $tmpvname = $vehicletypes[$histor->vehicleId]; @endphp
@php $tmpvtype = $servicetypes[$histor->serviceType]; @endphp
<tr>
<td>{{ $tmpvname->name }}</td>
<td>{{ $histor->serviceDate }}</td>
<td>{{ $tmpvtype->name }}</td>
<td>{{ $histor->vehicleKm }} km</td>
<td>{{ $histor->serviceDetails }}</td>

</tr>
@endforeach
</table>

</body>
</html>
</div>
</body>
</html>