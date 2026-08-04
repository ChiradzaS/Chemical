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
<br>
</div>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Create Production</h2>
</div>
<div class="pull-right">
<br>
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('productions.store') }}" method="POST" enctype="multipart/form-data">
@csrf


<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />





<strong>Process:</strong>
<select  id="processId" name="processId" class="js-example-basic-single"  >
<option value="" disabled selected hidden>-- select process Type --</option>
@foreach($processtypes as $processtype)
<option value="{{ $processtype->id }}"   >{{ $processtype->name }} </option>
@endforeach
</select>
@error('processId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
<br>

<strong>Machine:</strong>
<select  id="machineryId" name="machineryId"  class="js-example-basic-single"  >
<option value="" disabled selected hidden>-- select machine --</option>
@foreach($machinetypes as $machinetype)
<option value="{{ $machinetype->id }}"  >{{ $machinetype->name }} </option>
@endforeach
</select>
@error('machineryId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
<br>

<strong>Employee :</strong>
<select  id="employeeId" name="employeeId" class="js-example-basic-single"  >
<option value="" disabled selected hidden>-- select employee --</option>
@foreach($employees as $employee)
<option value="{{ $employee->id }}"  >{{ $employee->name }} </option>
@endforeach
</select>
@error('employeeId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
<br>

<strong>Other worker:</strong>
<input type="text" name="user"><br>


<strong>Shift:</strong>
    <select  id="shiftId" name="shiftId"  class="js-example-basic-single"  >
    <option value="" disabled selected hidden>-- select shift --</option>
@foreach($shifttypes as $shifttype)
<option value="{{ $shifttype->id }}"  >{{ $shifttype->name }} </option>
@endforeach
</select>
@error('shiftId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror<br>


<strong>Start Date:</strong>
<input type="date" name="prodDate" id="prodDate"  value="<?php echo date('Y-m-d'); ?>"><br>
<button type="submit" class="btn btn-outline-info">Save</button>
</body>
</html>