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
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Edit Order </h2>
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

@csrf
@method('PUT')
<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<strong>Customer:</strong>
<select name="customerId" id="customers"   class="form-control form-control-sm"   placeholder="-- Select Customer --">
@foreach($customers as $customer)
<option value="{{ $customer->id }}" @if($customer->id==$ordercustomer->customerId) selected @endif >{{ $customer->name }}</option>
@endforeach
</select>
</div>
</div>
@php Log::info(" yyyyy 1 Edit --- orders ------------------------------------------- : ");  @endphp
<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Reference:</strong>
<input type="text" name="reference"  class="form-control form-control-sm" value="{{ $ordercustomer->reference }}" placeholder="Reference">
@error('reference')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Other:</strong>
<input type="text" name="other" class="form-control form-control-sm" value="{{ $ordercustomer->other }}" placeholder="Other">
@error('other')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>totalValue:</strong>
<input type="text" name="totalValue" class="form-control form-control-sm" value="{{ $ordercustomer->totalValue }}" placeholder="totalValue">
@error('totalValue')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Status:</strong>
<input type="text" name="status" class="form-control form-control-sm" value="{{ $ordercustomer->status }}" placeholder="status">
@error('status')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<br>




</form>
</div>
<div><a class="btn btn-outline-info" href="{{ route('customerorderitems.create', ['ordersId' => $ordercustomer->id]) }}">Add</a></div>
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
        <th  scope="col"> Price</th>
        <th  scope="col"> Outstanding</th>
       
        <th  scope="col" width="400px"> Action</th>
        </tr>
       

        @foreach ($customerorderitems as $customerorderitem)
        @php $tmpProduct = $porducts[$customerorderitem->productId]; @endphp
        @php $tmpUnittype = $unittypes[$customerorderitem->unitId]; @endphp
       
        <tr>
     
        <td>{{ $customerorderitem->id }}</td>
        <td>{{ $customerorderitem->ordersId }}</td>
        <td>{{ $tmpProduct->name }}</td>
        <td>{{ $tmpUnittype->name }}</td>
        <td>{{ $customerorderitem->quantity }}</td>
        <td>{{ $customerorderitem->price }}</td>
        <td></td>
       
    <td>

    <a class="btn btn-outline-info" href="{{ route('ordercustomers.show',$ordercustomer->id) }}">View</a>   
    <a class="btn btn-outline-info" href="{{ route('ordercustomers.edit',$ordercustomer->id) }}">Update</a>
    @csrf
    @method('DELETE') 
    <button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
    <a class="btn btn-outline-dark" href="{{ route('job_cards.create',['order' =>'create', 'product' => $ordercustomer->productId ,'orderId' => $ordercustomer->ordersId, 'ordercustomerId' => $ordercustomer->id ]) }}">Create Jobcard</a>  
    </form>
    @endforeach
</body>
</html>