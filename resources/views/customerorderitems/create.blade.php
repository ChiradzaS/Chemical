<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->


<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Script CDN -->


<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
$(document).ready(function(){
 
$("#productId").change(function(){

var cboProduct = document.getElementById("productId");
var aproductId = cboProduct.options[cboProduct.selectedIndex].value;

var productid = Number(aproductId);
//alert(" P No : "+productid);

if(productid > 0){
 //alert(" Create > 0 : "+productid);

// AJAX POST request
$.ajax({
url: "{{ route('getProductbyid') }}",
type: 'post',
data: {_token: CSRF_TOKEN, productid: productid},
dataType: 'json',
success: function(response){
 //window.alert("Get User Id");
 setProduct(response);

}
});
}


});



});

function multiply() {
    var valPrice = document.getElementById("price").value;  
    //window.alert(" Price : "+valPrice);
    var valQuantity = document.getElementById("quantity").value;
    
    var varTotalPrice = valPrice * valQuantity;
    //window.alert(" Total Price : "+varTotalPrice);
    const tPrice = document.getElementById("totalPrice");
    tPrice.value = varTotalPrice;
}

function setProduct(response){
if(response['data'] != null){
len = response['data'].length;
}
if (len > 0) {
// alert(len);
for(var i=0; i<len; i++){
var id = response['data'][i].id;
var name = response['data'][i].name;

var defaultSellingPice = response['data'][i].defaultSellingPice;
var actualSellingPrice = response['data'][i].actualSellingPrice;
var unitTypeId = response['data'][i].unitTypeId;

document.getElementById("unitId").value = "" + unitTypeId;
document.getElementById("price").value = "" + actualSellingPrice;

var idName = "Product "+id+"  " + name + " , Default Selling Price: " + defaultSellingPice+" , Actual: " + actualSellingPrice + " , unitTypeId: " + unitTypeId;
// alert(idName);
}
}

}

</script>

</head>
<body>
<div>
{{-- @include('view') --}}
</div>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Add Order iterm</h2>
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
<form action="{{ route('customerorderitems.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<table class="row">
<strong>Product</strong>
<div class="col-xs-12 col-sm-12 col-md-12">
<select  id="productId" name="productId" class="form-control form-control-sm" placeholder="-- Select Product --">
@foreach($porducts as $porduct)
<option value="" disabled selected hidden>-- select product name --</option>
<option value="{{ $porduct->id }}" >{{ $porduct->name }}</option>
@endforeach
</select>
</div>
<strong>Unit Type</strong>
<div class="col-xs-12 col-sm-12 col-md-12">
<select id="unitId" name="unitId" class="form-control form-control-sm"  placeholder="-- Select unit Type --" >
@foreach($unittypes as $unittype)
<option value="" disabled selected hidden>-- select unit type --</option>
<option value="{{ $unittype->id }}" >{{ $unittype->name }}</option>
@endforeach
</select>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Quantity</strong>
<input name="quantity"  id="quantity" class="form-control form-control-sm" placeholder="Quantity">
@error('quantity')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Order id</strong>
<input   name="ordersId"  class="form-control form-control-sm" value="{{$ordersId}}" >
@error('ordersId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>other</strong>
<textarea  name="other" class="form-control form-control-sm" placeholder="other"></textarea>
@error('other')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Price</strong>
<input name="price"  id="price" class="form-control form-control-sm" placeholder="price" >
@error('price')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Total Cost</strong>
<input name="totalPrice"  id="totalPrice" class="form-control form-control-sm" placeholder="total">
@error('totalPrice')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<button type="submit" class="btn btn-outline-info">Save</button>
</body>
</html>