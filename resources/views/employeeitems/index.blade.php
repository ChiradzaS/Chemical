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
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h3>Add New Production item</h3>
<br>
<br>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<a class="btn btn-outline-success" href="{{ route('employeeitemss.create') }}">  Create New  </a>
@if ($message = Session::get('success'))
<div class="alert alert-success">
<p>{{ $message }}</p>
</div>
@endif
<table class="table table-striped" >
    <tr>
        <th  scope="col"> Id No</th>
        <th  scope="col"> production id</th>
        <th  scope="col"> JobcarditemId</th>
        <th  scope="col"> Product</th>
        <th  scope="col"> Serial No</th>
        <th  scope="col"> Quantity</th>
        <th  scope="col"> Unit</th>
        <th  scope="col"> Employee</th>
        <th  scope="col"> Machine</th>
        <th  scope="col"> Shift</th>
        <th  scope="col" width="200px"> Action</th>
        </tr>
        @foreach ($employeeitemss as $employeeitems)
        <tr>
        <td>{{ $employeeitems->id }}</td>
        <td>{{ $employeeitems->productionId }}</td>
        <td>{{ $employeeitems->jobcarditemId }}</td>
        <td>{{ $employeeitems->productId }}</td>
        <td>{{ $employeeitems->serialNo }}</td>
        <td>{{ $employeeitems->qnt }}</td>
        <td>{{ $employeeitems->qntUnitId }}</td>
        <td>{{ $employeeitems->employeeId }}</td>
        <td>{{ $employeeitems->machineryId }}</td>
        <td>{{ $employeeitems->shiftId }}</td>
        <td>
<form action="{{ route('employeeitemss.destroy',$employeeitems->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('employeeitemss.edit',$employeeitems->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
</form>
</td>
</tr>
@endforeach
</table>
{!! $employeeitemss->links() !!}
</body>
</html>