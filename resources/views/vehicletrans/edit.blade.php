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
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
    <br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >View Pump Transcation</th>

</tr>
</thead>
</table>
<br>
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

<style>
  .centred {
    text-align: center;
  }
</style>
<table class="table table-bordered">
  <thead>
    <tr>
      <th  class="centred text-center" >Transaction</th>
      <th  class="centred text-center">Details</th>

 
    </tr>
  </thead>
  <tbody>
  <tr>
        <th>Date</th>
      <td>{{$trans->created_at}}</td>
      
     
    </tr>
    <tr>
    <tr>
        <th>Vehicle Milage</th>
      <td>{{$trans->vehicleKm}} km</td>
      
     
    </tr>
    <tr>

      <th>Litres</th>
      <td>{{$trans->litres}} ltr</td>
      

    </tr>
    <tr>

<th>Pump</th>
<td ><select  name="pumpId" id="pumpId"  name="pumpId" id="pumpId" class="form-control form-control-sm" disabled>
    @foreach($pumps as $pump)
    <option value="{{$pump->id}}" @if($pump->id==$trans->pumpId) selected @endif >{{ $pump->name }} </option>
    @endforeach
    </select></td>


</tr>
<tr>

<th>Driver</th>
<td><select name="driverId" id="driverId"  class="form-control form-control-sm" disabled>
    @foreach($drivers as $driver)
    <option value="{{$driver->id}}" @if($driver->id==$trans->driverId) selected @endif>{{ $driver->name }} </option>
    @endforeach

    </select></td>


</tr>
<tr>
    <th>Vehicle</th>
    <td>

    <select  name="vehicleId" id="vehicleId"   class="form-control form-control-sm" disabled>
  <option value="0"  class="centered">-----select Vehicle---- </option>
    @foreach($vehicles as $vehicle)
    <option value="{{$vehicle->id}}" @if($vehicle->id==$trans->vehicleId) selected @endif>{{ $vehicle->name }} </option>
    
    @endforeach

    </select>
    </td>
</tr>

<tr>
    <th>Fuel Type</th>
    <td>

    <select  name="fuelId" id="fuelId"   class="form-control form-control-sm" disabled>
    @foreach($fuels as $fuel)
    <option value="{{$fuel->id}}" >{{ $fuel->name }} </option>
    @endforeach
    </select>
    </td>
</tr>

  
  </tbody>
</table>
<br>
<a class="btn btn-outline-info" href="{{ route('vehicletrans.index') }}" enctype="multipart/form-data">&nbsp;&nbsp;List&nbsp;&nbsp;</a>
</div>
</form>
</div>

</body>
</html>