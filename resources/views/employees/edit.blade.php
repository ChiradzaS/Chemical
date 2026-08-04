<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<meta charset="UTF-8">

</head>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2"> 
<h2>Edit Employees</h2>
</div>
<div class="pull-right">
<a class="btn btn-primary" href="{{ route('employees.index') }}" encemployee="multipart/form-data"> Back</a>
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('employees.update',$employee->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Employee Name:</strong>
<input type="text" name="name" class="form-control form-control-sm" value="{{ $employee->name }}" >
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Employee Role:</strong>
<select id="jobId" name="jobId"  class="form-control form-control-sm" placeholder="-- Select Employee Role --">
@foreach($jobtypes as $jobtype)
<option value="{{$jobtype->id}}" @if ($jobtype->id==$employee->jobId) selected @endif>{{$jobtype->name}}</option>
@endforeach
</select>
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Employee Surname:</strong>
<input type="text" name="surname" class="form-control form-control-sm" value="{{ $employee->surname }}" >
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Employee Date Of Birth:</strong>
<input type="text" name="dateOfBirth" class="form-control form-control-sm" value="{{ $employee->dateOfBirth }}" >
@error('dateOfBirth')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Employee Start Of Job:</strong>
<input type="text" name="startOfJob" class="form-control form-control-sm" value="{{ $employee->startOfJob }}" >
@error('startOfJob')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Nationality:</strong>
<input type="text" name="nationality" class="form-control form-control-sm" value="{{ $employee->nationality }}" >
@error('nationality')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong> Identity No:</strong>
<input type="text" name="identityNo" class="form-control form-control-sm" value="{{ $employee->identityNo }}" >
@error('identityNo')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>initials:</strong>
<input type="text" name="initials" class="form-control form-control-sm" value="{{ $employee->initials }}" >
@error('initials')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>nickName:</strong>
<input type="text" name="nickName" class="form-control form-control-sm" value="{{ $employee->nickName }}" >
@error('nickName')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>uniqueIdentifiableName:</strong>
<input type="text" name="uniqueIdentifiableName" class="form-control form-control-sm" value="{{ $employee->uniqueIdentifiableName }}" >
@error('uniqueIdentifiableName')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>documentNo:</strong>
<input type="text" name="documentNo" class="form-control form-control-sm" value="{{ $employee->documentNo }}" >
@error('documentNo')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>documentType:</strong>
<input type="text" name="documentType" class="form-control form-control-sm"  value="{{ $employee->documentType }}" >
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>documentTypeId:</strong>
<input type="text" name="documentTypeId" class="form-control form-control-sm" value="{{ $employee->documentTypeId }}" >
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>healthComments:</strong>
<input type="text" name="healthComments" class="form-control form-control-sm" value="{{ $employee->healthComments }}" >
@error('healthComments')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>postalAddress:</strong>
<input type="text" name="postalAddress" class="form-control form-control-sm" value="{{ $employee->postalAddress }}" >
@error('postalAddress')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>contactNo:</strong>
<input type="text" name="contactNo" class="form-control form-control-sm" value="{{ $employee->contactNo }}" >
@error('contactNo')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>cellPhoneNo:</strong>
<input type="text" name="cellPhoneNo" class="form-control form-control-sm" value="{{ $employee->cellPhoneNo }}" >
@error('cellPhoneNo')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>gender:</strong>
<input type="text" name="gender" class="form-control form-control-sm" value="{{ $employee->gender }}" >
@error('gender')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>physicalAddress:</strong>
<input type="text" name="physicalAddress" class="form-control form-control-sm"  value="{{ $employee->physicalAddress }}" >
@error('physicalAddress')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Password:</strong>
<input type="password" name="password" value="{{ $employee->password }}" class="form-control form-control-sm" >
@error('password')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>dateOftermination':</strong>
<input type="text" name="dateOftermionination" class="form-control form-control-sm" value="{{ $employee->dateOftermionination }}" >
@error('dateOftermination')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>other</strong>
<input type="text" name="other'" class="form-control form-control-sm" value="{{ $employee->other }}" >
@error('other')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<button employee="submit" class="btn btn-outline-info">Submit</button>
</div>
</form>
</div>
</body>
</html>