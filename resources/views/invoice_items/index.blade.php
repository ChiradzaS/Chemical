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
<!DOCTYPE html>
<html lang="en">
 </body>
</html>
<h3>Add New Order item</h3>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<a class="btn btn-outline-success" href="{{ route('order_items.create') }}">  Create New  </a>
@if ($message = Session::get('success'))
<div class="alert alert-success">
<p>{{ $message }}</p>
</div>
@endif
<table class="table table-striped" >
<tr>
<th  scope="col"> OrderIterm no</th>
<th  scope="col"> Order Reference</th>
<th  scope="col"> Product Type</th>
<th  scope="col"> Quantity</th>
<th  scope="col"> Other info</th>
<th  scope="col"> Price</th>
<th  scope="col"> Unit Type</th>
<th  scope="col"> Total Cost</th>

<th  scope="col" width="200px"> Action</th>
</tr>
@foreach ($order_items as $order_item)
 @php $tmpProduct = $porducts[$order_item->productId]; @endphp
@php $tmpUnittype = $unittypes[$order_item->unitId]; @endphp 
<tr>
<td>{{ $order_item->id }}</td>
<td>{{ $order_item->ordersId }}</td>
<td>{{ $tmpProduct->name }}</td>
<td>{{ $order_item->quantity }}</td>
<td>{{ $order_item->other }}</td>
<td>{{ $order_item->price }}</td>
<td>{{ $tmpUnittype ->name}}</td>
<td>{{ $order_item ->totalPrice}}</td>
<td>
<form action="{{ route('order_items.destroy',$order_item->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('order_items.edit',$order_item->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
</form>
</td>
</tr>
@endforeach
</table>
{!! $order_items->links() !!}
</body>
</html>
