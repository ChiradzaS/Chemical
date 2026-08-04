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

<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
{{-- @include('view') --}}
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2"> 


    <br>
    @if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success! </strong>{{ $message }}
</div>
@endif


@if ($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Failed!</strong> {{ $message }}
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

    .alert.alert-danger.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }
</style>

    <br>
    <a class="btn btn-outline-success" href="{{ route('employees.create') }}"> Create New</a>
    <br>
    <br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Employee List</th>
</tr>
</thead>
</table>
</div>

</div>
</div>

<form action="{{ route('porducts.index')}}" method="GET">

<table class="table table-striped">
<tr>
<th  scope="col">Id</th>
<th  scope="col">Name</th>
<th  scope="col">Surname</th>
<th  scope="col">Start of Job</th>            
<th  scope="col">Job / Role</th>    
<th  scope="col" width="300px"> Action</th>
</tr>
@foreach ($employees as $employee)
@php $tmpjobtype = $jobtypes[$employee->jobId]; @endphp
<tr>
<td>{{ $employee->id }}</td>
<td>{{ $employee->name }}</td>
<td>{{ $employee->surname }}</td>
<td>{{ $employee->startOfJob }}</td>
<td>{{ $tmpjobtype->name }}</td>
<td>
<form action="{{ route('employees.destroy',$employee->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('employees.edit',$employee->id) }}">update</a>
@csrf
@method('DELETE')
<button employee="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete')">Delete</button>
</form>
</td>
</tr>
@endforeach
</table>
{!! $employees->links() !!}
</body>
</html>