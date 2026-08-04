<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->


<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Script CDN -->


<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
$(document).ready(function(){

$("#userTypeId").change(function(){

var cboUserType = document.getElementById("userTypeId");

var aUserTypeId = cboUserType.options[cboUserType.selectedIndex].value;
//alert(" 0 User Type : " + aUserTypeId);
var userType = cboUserType.options[cboUserType.selectedIndex].text;
//alert(" Text User Type : " + userType);

if(aUserTypeId > 0){
// alert("> 0");

// AJAX POST request
$.ajax({
url: "{{ route('getUserTypeList') }}",
type: 'post',
data: {_token: CSRF_TOKEN, userType: userType},
dataType: 'json',
success: function(response){
//window.alert("-- User Type --");
setUserType(response);

}
});
}


});



});

function setUserType(response){
if(response['data'] != null){
len = response['data'].length;
}
if (len > 0) {
// alert(len);
  var html = '';
  for(var i = 0; i < len; i++){
     html += '<option value="' + response['data'][i].id + '">' + response['data'][i].name + '</option>';
  }

  document.getElementById('userTypeById').innerHTML = html;

}

}

</script>
<title>Add Users Form - Laravel 8 CRUD</title>

</head>
<body>
<div>
</div>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Add User Details</h2>
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
<form action="{{ route('userdetails.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<strong>User Type :</strong>

<select id="userTypeId" name="userTypeId" class="form-control form-control-sm"  placeholder="-- Select unit Type --">
@foreach($usertypes as $usertype)
<option value="" disabled selected hidden>-- select user --</option>
<option value="{{ $usertype->id }}"  >{{ $usertype->name }}</option>
@endforeach
</select>


<strong>User Id :</strong>
<select id="userId" name="userId" class="form-control form-control-sm"  placeholder="-- Select user --">
@foreach($usersWithoutDetails as $usersWithoutDetail)
<option value="" disabled selected hidden>-- select user --</option>
<option value="{{$usersWithoutDetail->id }}">{{$usersWithoutDetail->name }}</option>
@endforeach
</select>

<strong>User Type Name By Id:</strong>
<select id="userTypeById" name="userTypeById"  class="form-control form-control-sm" placeholder="-- User Type by Id --">
@foreach($userTypeObjList as $userTypeObj)
<option value="{{$userTypeObj->id}}" >{{$userTypeObj->name}}</option>
@endforeach
</select>

<strong>User Name:</strong>
<input type="text" name="name"  class="form-control form-control-sm" placeholder="User Name">
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror

<strong>User Surname:</strong>
<input type="text" name="surname" class="form-control form-control-sm" placeholder="surname">
@error('surname')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror


<strong>User cellPhone:</strong>
<input type="text" name="cellPhone" class="form-control form-control-sm" placeholder="User cellPhone">
@error('address')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror


<strong>User telephone:</strong>
<input type="text" name="telephone"  class="form-control form-control-sm" placeholder="User telephone">


<strong>User email Address:</strong>
<input type="email" name="emailAddress" class="form-control form-control-sm" placeholder="User Email">
@error('emailAddress')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror


<strong>User Position:</strong>
<div class="input-group">
      <select  id="userPosition" name="userPosition"  class="js-example-basic-single form-control"  >
      <option value="" disabled selected hidden>-- Select job --</option>
      @foreach($jobtypes as $jobtype)
      <option value="{{ $jobtype->id }}"  >{{ $jobtype->name }} </option>
      @endforeach
      </select>
    
  </div>
@error('userPosition')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror


<strong>User username:</strong>
<input type="text" name="userName" class="form-control form-control-sm" placeholder="username">
@error('text')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror



<strong>User security Level:</strong>
<input type="text" name="securityLevel"  class="form-control form-control-sm" >
@error('securityLevel')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror


<strong>Other:</strong>
<textarea name="other" id="other"   class="form-control form-control-sm" ></textarea><br>
@error('other')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror

<button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Submit</button> 
</div>
</form>
</body>
</html>