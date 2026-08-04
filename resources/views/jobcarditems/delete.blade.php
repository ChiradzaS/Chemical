<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
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
<h2>Update  JobCard  Item</h2>
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
<form action="{{ route('jobcarditems.update',$jobcarditem->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<strong>Unit type:</strong>
<select  name="unitId"  class="form-control form-control-sm">
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}"  @if($jobcarditem->unitId==$unittype->id) selected @endif>{{ $unittype->name }}</option>
@endforeach
</select>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<strong>Process type:</strong>
<select  name="processId"  class="form-control form-control-sm"    >
@foreach($processtypes as $processtype)
<option value="{{ $processtype->id }}"  @if($jobcarditem->processId==$processtype->id) selected @endif>{{ $processtype->name }}</option>
@endforeach
</select>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Job Card id:</strong>
<input type="text" name="jobCardId" value="{{ $jobcarditem->jobCardId}}" class="form-control form-control-sm" >
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>




<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Job Card name:</strong>
<input type="text" name="name" value="{{ $jobcarditem->name }}" class="form-control form-control-sm" placeholder="Company name">
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>


<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Quantity:</strong>
<input type="text" name="qnt" value="{{ $jobcarditem->qnt }}"class="form-control form-control-sm" placeholder="Company name">
@error('name')
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