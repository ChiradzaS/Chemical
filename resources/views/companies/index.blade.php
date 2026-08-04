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


<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<!DOCTYPE html>
<html lang="en">
 </body>
</html>
<h3>Add New Company</h3>
<a class="btn btn-outline-primary" href="{{ route('companies.create') }}"> Create New</a>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>

<table class="table table-striped" >
<tr>
<th  scope="col"> S.No</th>
<th  scope="col"> Company Name</th>
<th  scope="col"> Company Email</th>
<th  scope="col"> Company Address</th>
<th  scope="col" width="200px"> Action</th>
</tr>
@foreach ($companies as $company)
<tr>
<td>{{ $company->id }}</td>
<td>{{ $company->name }}</td>
<td>{{ $company->email }}</td>
<td>{{ $company->address }}</td>
<td>
<form action="{{ route('orders.destroy', 95) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('companies.edit',$company->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>

</form>
</td>
</tr>
@endforeach
</table>
{!! $companies->links() !!}
</body>
</html>