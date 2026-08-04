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
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<!DOCTYPE html>
<html lang="en">
 </body>
</html>

<h3>Add New Order</h3>

</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
@if ($message = Session::get('success'))
<div class="alert alert-success">
<p>{{ $message }}</p>
</div>
@endif
<table class="table table-striped" >
<tr>
<th  scope="col"> Order_No</th>
<th  scope="col"> Customer</th>
<th  scope="col"> Other</th>
<th  scope="col"> Order Reference</th>
<th  scope="col"> Total Value</th>
<th  scope="col"> Status</th>
<th  scope="col" width="300px"> Action</th>
</tr>
@foreach ($ordercustomers as $ordercustomer)
@php $tmpCustomer = $customers[$ordercustomer->customerId]; @endphp
<tr>
<td>{{ $ordercustomer->id }}</td>
<td>{{ $tmpCustomer->name }}</td>
<td>{{ $ordercustomer->other }}</td>
<td>{{ $ordercustomer->reference }}</td>
<td>{{ $ordercustomer->totalValue }}</td>
<td>{{ $ordercustomer->state }}</td>
<td>

<a class="btn btn-outline-info" href="{{ route('ordercustomers.show',$ordercustomer->id) }}" >View </a>     
<a class="btn btn-outline-info" href="{{ route('ordercustomers.edit',$ordercustomer->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>

</form>
</td>
</tr>
@endforeach
</table>
{!! $ordercustomers->links() !!}
</body>
</html>