<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<meta name="csrf-token" content="{{ csrf_token() }}">


<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
$(document).ready(function(){
 
$("#productId").change(function(){

var cboProduct = document.getElementById("productId");
var aproductId = cboProduct.options[cboProduct.selectedIndex].value;

var productid = Number(aproductId);
  //alert(" P No : "+productid);

if(productid > 0){
 //alert("> 0");

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

function setProduct(response){
if(response['data'] != null){
len = response['data'].length;
}
if (len > 0) {
 //alert(len);
for(var i=0; i<len; i++){
var id = response['data'][i].id;
var name = response['data'][i].name;

var defaultSellingPice = response['data'][i].defaultSellingPice;
var actualSellingPrice = response['data'][i].actualSellingPrice;
var unitTypeId = response['data'][i].unitTypeId;

document.getElementById("unitId").value = "" + unitTypeId;
document.getElementById("price").value = "" + actualSellingPrice;

var idName = "Product "+id+"  " + name + " , Default Selling Price: " + defaultSellingPice+" , Actual: " + actualSellingPrice + " , unitTypeId: " + unitTypeId;
 //alert(idName);
}
}

}

</script>
</head>
<body>
<div>
</div>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Add Production item</h2>
</div>
<div class="pull-right">
</div>
</div>
</div>
@if ($message = Session::get('success'))
<div class="alert alert-success">
<p>{{ $message }}</p>
</div>
@endif
<br>
<br>
<br>
<form action="{{ route('productionitems.create') }}">
<strong>Production id:</strong>
<input name="productionId"    value="{{$productionitem->productionId}}"  readonly >
<br>

<strong> Job Card item Id:</strong>
<input   name="jobcarditemId"   value="{{$productionitem->jobcarditemId}}"  readonly  > 

<br>
  <input type="radio" id="barcode" name="fetchId" value="barcode">
  <label for="barcode">Barcode</label>
  <input type="radio" id="jobcarditemId" name="fetchId" value="jobcarditemId">
  <label for="jobcarditemId">Job Card Item Id: </label><br>
  <input   name="jobcarditemInfo" >
  <button type="submit" name="myButton" value="fetchData" id="fetchData" class="btn btn-outline-info" >Fetch</button>
<br>

<strong>Product:</strong>
<select  id="productId" name="productId" >
@foreach($porducts as $porduct)
<option value="{{ $porduct->id }}" @if($porduct->id==$productionitem->productId) selected @endif >{{ $porduct->name }}</option>
@endforeach
</select><br>
  
   
<br>

<strong>Unit</strong>
<select  id="unitId" name="unitId"  >
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}"  @if($unittype->id==$productionitem->unitId) selected @endif >{{ $unittype->name }} </option>
@endforeach
</select>
<br>

<strong> Quantity Left :</strong>
<input   name="jobcarditemQnt"   value={{$jobcarditem->qnt}}  >

<strong> Enter Quantity:</strong>
<input   name="qnt"     >

<br>

<strong>Comment:</strong>
<input   name="other"    >
<br>

<strong>Weight:</strong>
<input name="weight"  ><br>

<br>
<button type="submit"  name="myButton" value="create" id="create" class="btn btn-outline-info">Save</button>
</form>
</body>
</html>