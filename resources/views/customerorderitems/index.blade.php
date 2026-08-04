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
<h3>Orders Items List</h3>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<div><a class="btn btn-outline-success" href="{{ route('order_items.create') }}">  Create New  </a></div><br>

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
<th  scope="col"> Price</th>
<th  scope="col"> Unit Type</th>




<th  scope="col" width="350px"> Action</th>
</tr>
@foreach ($customerorderitems as $customerorderitem)
 @php $tmpProduct = $porducts[$customerorderitem->productId]; @endphp
@php $tmpUnittype = $unittypes[$customerorderitem->unitId]; @endphp 
<tr>
<td>{{ $customerorderitem->id }}</td>
<td>{{ $customerorderitem->ordersId }}</td>
<td>{{ $tmpProduct->name }}</td>
<td>{{ $customerorderitem->quantity }}</td>
<td>{{ $customerorderitem->price }}</td>
<td>{{ $tmpUnittype ->name}}</td>






@if ( $customerorderitem ->job_card_id > 0)

<td>
<form action="{{ route('customerorderitems.destroy',$customerorderitem->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('customerorderitems.edit',$customerorderitem->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
<a class="btn btn-outline-dark" href="{{ route('job_cards.index') }}"> Jobcard Info</a>
</form>
</td>
@else
<td>
<form action="{{ route('customerorderitems.destroy',$customerorderitem->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('customerorderitems.edit',$customerorderitem->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
<a class="btn btn-outline-dark" href="{{ route('job_cards.create',['order' =>'create', 'product' => $customerorderitem->productId ,'orderId' => $customerorderitem->ordersId, 'orderitemId' => $customerorderitem->id ]) }}">Create Jobcard </a>
</form>
</td>
@endif





</tr>
@endforeach
</table>
{!! $customerorderitems->links() !!}
</body>
</html>
