<!DOCTYPE html>
<html>

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

<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Script CDN -->


<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
$(document).ready(function(){

$("#productId").change(function(){
  ///window.alert("Get User Id");

  var cboProduct = document.getElementById("productId");
var aproductId = cboProduct.options[cboProduct.selectedIndex].value;

var productid = Number(aproductId);
//alert(" P No : "+productid);
 

if(productid > 0){
 //alert("> 0");

// AJAX POST request
$.ajax({
url: "{{ route('getProductbyidForOrderItem') }}",
type: 'post',
data: {_token: CSRF_TOKEN, productid: productid},
dataType: 'json',
success: function(response){

  //window.alert(" PRODUCT ");
  setProduct(response);

}
});
}




});

});

function setProduct(response){
if(response['data'] != null){
len = response['data'].length;
}
if (len > 0) {
 //alert(len);

 for(var i=0; i<len; i++){
var id = response['data'][i].id;
var name = response['data'][i].name;

var unitTypeId = response['data'][i].unitTypeId;
var actualSellingPrice = response['data'][i].actualSellingPrice;



document.getElementById("unitTypeId").value = "" + unitTypeId;
document.getElementById("actualSellingPrice").value = "" + actualSellingPrice;

}
}
}

</script>
<title>Create  Order </
</head>

<body>

<h3>Place Your Order </h3>
<br>
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
<form action="{{ route('customerorders.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<table class="table table-striped" >
<td width="400px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Product</span>
  </div>
  <select  id="productId" name="productId" class="form-control" placeholder="-- Select Product --">
@foreach($porducts as $porduct)
<option value="" disabled selected hidden>-- select product name --</option>
<option value="{{ $porduct->id }}" >{{ $porduct->name }}</option>
@endforeach
</select>
</div>
</td>
<td width="300px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1" >Unit</span>
  </div>
  <select  id="unitTypeId" name="unitId" class="form-control"  readonly>
  @foreach($unittypes as $unittype)
<option value="" disabled selected hidden>-Unit-Type-</option>
<option value="{{ $unittype->id }}" >{{ $unittype->name }}</option>
@endforeach
</div>

</td>
<script>

function report(){

  var qnt = document.getElementById("quantity").value;
   if ( qnt.length < 1 ){

    alert('Please enter the quantity');
    return ;

   }

}
  
</script>
<td width="300px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text bg-info text-white" id="basic-addon1">Quantity</span>
  </div>
  <input type="text" name="quantity" id="quantity"  class="form-control"  placeholder="Enter Quantity"  >
</div>

</td>
<td width="300px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Price</span>
  </div>
  <input type="text" name="price" id="actualSellingPrice"  class="form-control"   >
</div>
</td>
<td width="50px">
<div class="input-group mb-3">
  
<input type="hidden" name="ordersId" id="ordersId" value='101'    >
<input type="hidden" name="totalPrice" id="totalPrice" value={{0.00}} class="form-control"   >
</div>
</td>
<td>
<button type="submit"  class="btn btn-lg btn-primary" onclick="report()" >Add To List</button><br>
</td><br>
</form>
<br>
</table>


<table class="table table-striped" >
<tr>
<th class="text-center"   scope="col" width="400px"> Date</th>
<th class="text-center"   scope="col" width="400px"> Product</th>
<th  class="text-center"  scope="col" width="300px"> Unit </th>
<th  class="text-center"  scope="col" width="300px"> Previous Quantity</th>
<th class="text-center"   scope="col" width="150px"> Quantity</th>
<th  class="text-center"  scope="col" width="200px"> Price</th>
<th class="text-center"  class="text-center" scope="col" width="200px"> Add to Order</th>
</tr>

@foreach ($customerorderitems as $customerorderitem)
@php $tmpProduct = $porducts[$customerorderitem->productId]; @endphp
@php $tmpUnittype = $unittypes[$customerorderitem->unitId]; @endphp 
<tr>
<form action="{{ route('customerorders.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<table class="table table-striped" >
<td width="300px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Date</span>
  </div>
  <input type="text" class="form-control" value="{{$customerorderitem->created_at}}"   >
</div>
</td>
<td width="400px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Product</span>
  </div>
  <input type="text" id="productId" class="form-control"   value="{{$tmpProduct->name}}"   >
</div>
</td>
<td width="200px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Unit</span>
  </div>
  <input type="text" id="unitTypeId"  value="{{$tmpUnittype->name}}" class="form-control"    >
</div>
</td>
<td width="300px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Previous Quantity</span>
  </div>
  <input type="text"  value="{{$customerorderitem->quantity}}" class="form-control"    >
</div>

</td>
<td width="250px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span  class="input-group-text bg-info text-white" id="basic-addon1">Quantity</span>
  </div>
  <input  type="text"  name="quantity" id="quantity"  placeholder="Enter Quantity" class="form-control" 
  >    
</div>
</td>
<td width="200px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1">Price</span>
  </div>
  <input type="text" name="price" id="price" value="{{$customerorderitem->price}}" class="form-control"   >
</div>
</td>
<td width="50px">
<div class="input-group mb-3">
  
<input type="hidden" name="ordersId" id="ordersId" value='101'    >
<input type="hidden" name="price" id="price" value={{0.00}} class="form-control" >
<input type="hidden" name="unitId" value="{{$customerorderitem->unitId}}" class="form-control"  > 
<input type="hidden" name="productId" value="{{$customerorderitem->productId}}" class="form-control"  >   
 
</div>
</td>

<td>
<button type="submit"  class="btn btn-lg btn-primary" >Add </button><br>
</td>
</form>
  @endforeach
</table>
</body>
</html>