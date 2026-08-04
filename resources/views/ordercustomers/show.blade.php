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
@php Log::info(" Body 1 Edit --- orders ------------------------------------------- : ");  @endphp
<body>
{{-- @include('view') --}}
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>View Order </h2>
</div>
<div class="pull-right">
</div>
</div>
</div>
<div>
<div>
<button class="btn btn-outline-info"  onclick="javascript:window.history.back();">Go Back</button>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('orders.update',$order->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
<div class="row">
<strong>Customer:</strong>
<div class="col-xs-12 col-sm-12 col-md-12">
<select name="customerId" id="customers"   class="form-control form-control-sm"   placeholder="-- Select Customer --" readonly>
@foreach($customers as $customer)
<option value="{{ $customer->id }}" @if($customer->id==$order->customerId) selected @endif >{{ $customer->name }}</option>
@endforeach
</select>
</div>
</div>
@php Log::info(" yyyyy 1 Edit --- orders ------------------------------------------- : ");  @endphp
<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Reference:</strong>
<input type="text" name="reference"  class="form-control form-control-sm" value="{{ $order->reference }}" placeholder="Reference" readonly>
@error('reference')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Other:</strong>
<input type="text" name="other" class="form-control form-control-sm" value="{{ $order->other }}" placeholder="Other" readonly>
@error('other')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>totalValue:</strong>
<input type="text" name="totalValue" class="form-control form-control-sm" value="{{ $order->totalValue }}" placeholder="totalValue" readonly>
@error('totalValue')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Status:</strong>
<input type="text" name="status" class="form-control form-control-sm" value="{{ $order->status }}" placeholder="status" readonly>
@error('status')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<br>

</form>
</div>
<br>

@php Log::info(" Style 1 Edit --- orders ------------------------------------------- : ");  @endphp

<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}
</style>
<hr>
<h3>Order items</h3>
<table class="table table-striped" >
    <tr>
        <th  scope="col"> OrderIterm no</th>
        <th  scope="col"> Order Reference</th>
        <th  scope="col"> Product Type</th>
        <th  scope="col"> Unit Type</th>
        <th  scope="col"> Quantity</th>
        <th  scope="col"> Other info</th>
        <th  scope="col"> Price</th>
        <th  scope="col"> Total Cost</th>
       
      
        </tr>
        @php Log::info(" Style 2 Edit --- orders ------------------------------------------- : ");  @endphp

        @foreach ($orderitems as $orderitem)
        @php $tmpProduct = $porducts[$orderitem->productId]; @endphp
        @php $tmpUnittype = $unittypes[$orderitem->unitId]; @endphp
        @php Log::info(" Style 3 Edit --- orders ------------------------------------------- : ");  @endphp
        <tr>
     
        <td>{{ $orderitem->id }}</td>
        <td>{{ $orderitem->ordersId }}</td>
        <td>{{ $tmpProduct->name }}</td>
        <td>{{ $tmpUnittype->name }}</td>
        <td>{{ $orderitem->quantity }}</td>
        <td>{{ $orderitem->other }}</td>
        <td>{{ $orderitem->price }}</td>
        <td>{{ $orderitem->totalPrice }}</td>
       
    <td>
   
    </form>
    @endforeach
</body>
</html>