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

<title>Vehicle Maintanance</title>






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
<th>Add Maintanance Schedule</th>
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
<form action="{{ route('vehiclemaintanances.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
    <div class="form-group">
        <strong>Vehicle Type</strong>
        <span class="form-control form-control-sm" >
            @foreach($vehicletypes as $vehicletype)
                @if ($vehicleId == $vehicletype->id)
                    {{ $vehicletype->name }}
                @endif
            @endforeach
        </span>
    </div>
</div>



<input type="hidden" name="vehicleId" id="vehicleId"  value="{{$vehicleId }}" >



<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Service Date</strong>
<input type="date" name="serviceDate" id="serviceDate"  class="form-control form-control-sm" placeholder="">
@error('serviceDate')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Service Type</strong>
<select   name="serviceType" id="serviceType"  class="form-control form-control-sm"  >
<option value="" disabled selected hidden>-- select service Type --</option>
@foreach($servicetypes as $servicetype )
<option value="{{ $servicetype->id }}"   >{{ $servicetype->name }} </option>
@endforeach
</select>
@error('serviceType')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>



<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Vehicle Km</strong>
<input type="text" name="vehicleKm" id="vehicleKm" class="form-control form-control-sm" placeholder="">
@error('vehicleKm')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Detailed Service Information</strong>
<textarea id="serviceDetails" name="serviceDetails" rows="4" cols="50" class="form-control form-control-sm" placeholder="Details">
        
        </textarea>

@error('serviceDetails')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<script>
    function check(){



        var vehiclemilage = document.getElementById("vehicleKm").value;

                        if(vehiclemilage  == 0){

                            alert('Please select enter vehicle milage ');
                            event.preventDefault();

                            }




        var date = document.getElementById("serviceDate").value;

            if(date == 0){

                alert('Please service date ');
                event.preventDefault();
        
                }


        var type = document.getElementById("serviceType").value;

            if(type == 0 ){

                alert('Please select the service type');
                event.preventDefault();

                }
                



                if (isNaN(vehiclemilage)) {

                    alert('Please enter a valid number for vehicle km');
                    event.preventDefault();
                }

                
            }
</script>


<br>
<button type="submit" padding-right=5px class="btn btn-primary btn-sm" onclick="check()" >Submit</button> 
</div>
</form>
</body>
</html>