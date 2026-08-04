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
<script src="{{ asset('public/js/script.js') }}" ></script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<br>
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">

<button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success !</strong> {{ $message }}
</div>
@endif


<script>
  function closeNotification() {
    var alertElement = document.querySelector('.alert');
    if (alertElement) {
        alertElement.style.display = 'none';

    }

    
}


</script>


<style>
  
    .alert.alert-success.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }
</style>
<br>
<a class="btn btn-outline-primary" href="{{ route('userdetails.create') }}"> Create New</a>
<br>
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th> User Details List </th>
</tr>
</thead>
</table>
<br>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<table class="table table-striped" >
<tr>
<th  scope="col"> Id</th>
<th  scope="col"> Username</th>
<th  scope="col"> Name</th>
<th  scope="col"> Surname</th>
<th  scope="col"> User Type</th> 
<th  scope="col"> Cellphone</th>   
<th  scope="col" width="300px"> Action</th>
</tr>
@foreach ($usersdetails as $userinfo)
@php $tmpUser = $usertypes[$userinfo->userTypeId]; @endphp
<tr>
<td>{{ $userinfo->id }}</td>
<td>{{ $userinfo->userName }}</td>
<td>{{ $userinfo->name }}</td>
<td>{{ $userinfo->surname }}</td>
<td>{{ $tmpUser->name }}</td>
<td>{{ $userinfo->cellPhone }}</td>
<td>
<a class="btn btn-outline-info" href="{{ route('userdetails.show',$userinfo->id) }}">View</a>    
<a class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')" href="{{ route('userdetails.edit',[$userinfo->id, 'action' => 'Delete']) }}">Delete</a>
<a class="btn btn-outline-info" href="{{ route('userdetails.edit',$userinfo->id) }}">Update</a>



</form>
</td>
</tr>
@endforeach
</table>
{!! $usersdetails->links() !!}
</body>
</html>