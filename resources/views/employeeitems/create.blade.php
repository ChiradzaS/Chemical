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
<h2>Add Employee item</h2>
</div>
<div class="pull-right">
</div>
</div>
</div>
<br>
<br>
<br>
<form action="{{ route('employeeitems.create') }}">

<input name="productionId"    value="{{$employeeitem->productionId}}"  hidden>

<input name="employeeId"    value="{{$employeeitem->employeeId}}"  hidden >
<br>


<input   name="jobcarditemId"   value="{{$employeeitem->jobcarditemId}}"  hidden > 



        <div class="form-row">
    <div class="form-group col-md-6">
      <strong for="inputEmail4">Enter JobCard </strong>
      <input type="text" class="form-control"  name="jobcarditemInfo" >
    </div>
  </div>
  <button type="submit" name="myButton" value="fetchData" id="fetchData" class="btn btn-outline-info" >Fetch</button>
  <div class="form-row">
    <div class="form-group col-md-6">
      <strong for="inputEmail4">Quantity</strong>
      <input type="text" class="form-control"  name="qnt" >
    </div>
  </div>

  <div class="form-row">
    <div class="form-group col-md-6">
      <strong for="inputEmail4">Weight</strong>
      <input type="text" class="form-control" name="weight"  >
    </div>
  </div>

  <div class="form-row align-items-center">
          <div class="form-group col-md-6">
            <strong class="mr-sm-2" for="inlineFormCustomSelect">Unit</strong>
            <select class="custom-select mr-sm-2"  id="productId" name="productId">
            <option selected>Choose...</option>
                @foreach($porducts as $porduct)
                <option value="{{ $porduct->id }}" @if($porduct->id==$employeeitem->productId) selected @endif >{{ $porduct->name }}</option>
                @endforeach
                </select>
          </div>
        </div>

        <div class="form-row align-items-center">
          <div class="form-group col-md-6">
            <strong class="mr-sm-2" for="inlineFormCustomSelect">Product</strong>
            <select class="custom-select mr-sm-2" id="unitId" name="unitId">
            <option selected>Choose...</option>
                @foreach($unittypes as $unittype)
                <option value="{{ $unittype->id }}"  @if($unittype->id==$employeeitem->unitId) selected @endif >{{ $unittype->name }} </option>
                @endforeach
                </select>
          </div>
        </div>

<br>
  
  <input type="radio" id="barcode" name="fetchId" value="barcode" hidden>
  
  <input type="radio" id="jobcarditemId" name="fetchId" value="jobcarditemId" checked hidden>
 





<input   name="jobcarditemQnt"   value='{{$jobcarditem->qnt}}'  hidden>





<input   name="other"    hidden>

<button type="submit"  name="myButton" value="create" id="create" class="btn btn-outline-info">Save</button>
</form>
</body>
</html>