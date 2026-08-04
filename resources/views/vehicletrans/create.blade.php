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

<title>Vehicle transcation</title>


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
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Create Fuel Transcation</th>
</tr>
</thead>
</table>
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
<form action="{{ route('vehicletrans.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row">


<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Pump </strong>
<select  name="pumpId" id="pumpId"  name="pumpId" id="pumpId" class="form-control form-control-sm">
    @foreach($pumps as $pump)
    <option value="{{$pump->id}}" >{{ $pump->name }} </option>
    @endforeach
    </select>

</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Fuel Type </strong>
<select  name="fuelId" id="fuelId"   class="form-control form-control-sm">
    @foreach($fuels as $fuel)
    <option value="{{$fuel->id}}" >{{ $fuel->name }} </option>
    @endforeach
    </select>

</div>
</div>


<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Driver</strong>
<select name="driverId" id="driverId"  class="form-control form-control-sm">
  <option value="0"  class="centered">-----Select Driver-----</option>
    @foreach($drivers as $driver)
    <option value="{{$driver->id}}" >{{ $driver->name }} </option>
    @endforeach

    </select>

</div>
</div>


<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Vehicle</strong>
<select  name="vehicleId" id="vehicleId"   class="form-control form-control-sm" >
  <option value="0"  class="centered">-----select Vehicle---- </option>
    @foreach($vehicles as $vehicle)
    <option value="{{$vehicle->id}}" >{{ $vehicle->name }} </option>
    @endforeach

    </select>


</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Vehicle Milage</strong>
<input type="text" name="vehicleKm"  id="vehicleKm"  class="form-control form-control-sm" >

</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Litres</strong>
<input type="text" name="litres" id="litres" class="form-control form-control-sm" >

</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Crrrent number of litres</strong>  <label for="floatingInputValue"> (Only add if you are certain of the fuel quantity)</label>
<input type="text" name="current" id="current" class="form-control form-control-sm" >


</div>
</div>
<br>

<script>
    function check(){



        var vehicle = document.getElementById("vehicleId").value;

        if(vehicle == 0){

            alert('Please select vehicle ');
            event.preventDefault();

            }

        var driver = document.getElementById("driverId").value;

            if(driver == 0){

                alert('Please select vehicle ');
                event.preventDefault();
        
                }


        var km = document.getElementById("vehicleKm").value;

            if(!km ){

                alert('Please add the km for the vehicle ');
                event.preventDefault();

                }

        var litres = document.getElementById("litres").value;

            if(!litres){

                alert('Please enter litres ');
                event.preventDefault();

                }


                if (isNaN(km)) {

                    alert('Please enter a valid number for vehicle km');
                    event.preventDefault();
                }

                if (isNaN(litres)) {

                alert('Please enter a valid number for litres');
                event.preventDefault();

                }
                
            }
</script>

<button type="submit" padding-right=5px onclick="check()" class="btn btn-primary btn-sm" >Save</button> 
</div>
</form>
</body>
</html>