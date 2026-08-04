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
</head>


    <meta name="csrf-token" content="{{ csrf_token() }}">

<script type='text/javascript'>

function generate() {

var product = document.getElementById("productId").value;


 if (product > 0){ 
    $.ajax({
    url: "{{ route('generate') }}",
    type: 'post',
    data: {_token: CSRF_TOKEN, product: product},
    dataType: 'json',
    success: function(response){
   
    setgenerated(response);
  
    }


})
}
}


function  setgenerated(response){
    if(response['data'] != null){
  
  var item = response['data'];
 
   document.getElementById("jobcarditem").value=item;

  }
  diableBtn()

}

function diableBtn(){

var myButton = document.getElementById('myButton');
var btn = document.getElementById("jobcarditem").value;

if ( btn > 0 ) {
  myButton.disabled = true; 
} else {
  myButton.disabled = false; 
}

var today = new Date(); 
        var selectedDate = document.getElementById("prodDate").value; 

        if (selectedDate === "") { 
        document.getElementById("prodDate").value = today.toISOString().slice(0,10); 
        }

}




var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
$(document).ready(function(){
  //alert(" P No : /////////");
$("#productId").change(function(){

var cboProduct = document.getElementById("productId");
var aproductId = cboProduct.options[cboProduct.selectedIndex].value;

var productid = Number(aproductId);
 // alert(" P No : "+productid);

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
var unitTypeId = response['data'][i].unitTypeId;

document.getElementById("unitId").value = unitTypeId;

//alert('vcbvbcvbcv'+unitId );
var idName = "Product "+id+"  " + name + " , Default Selling Price: " + defaultSellingPice+" , Actual: " + actualSellingPrice + " , unitTypeId: " + unitTypeId;
 //alert(idName);
}
}



}





</script>
<body>
<div>
<br>
</div>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Create Production Stock</h2>
</div>
<div class="pull-right">
<br>
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('productionitems.create') }}" >

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />




<input type="text" name="productionId" value="{{$productionId}} " id="productionId" hidden><br>

<strong>Product :</strong>
<select  id="productId" name="productId"  class="js-example-basic-single" >
<option value="0" disabled selected hidden>-- select product Type --</option>
@foreach($products as $product)
<option value="{{ $product->id }}"   >{{ $product->name }} </option>
@endforeach
</select>

<script>


</script>



<strong>Unit</strong>
<select  id="unitId" name="unitId" >
<option value="" disabled selected hidden>--unit--</option>
@foreach($unittypes as $unittype)
<option  value="{{ $unittype->id }}"   >{{ $unittype->name }} </option>
@endforeach
</select>
@error('unitId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror



&nbsp; &nbsp; &nbsp;
<button type="button" id="myButton" value="myButton" onclick="generate()" class="btn btn-outline-info">Genarate Jobcard </button>
<br>
<br>

<strong>JobCard :</strong>
<input type="text" name="jobcarditemId"  id="jobcarditem" ><br>

<br>
<strong> Quantity:</strong>
<input   name="qnt"    id="qnt"   readonly>
&nbsp;&nbsp;&nbsp;

<strong>unit:</strong>
<input   name="unit"    id="unit"  value readonly>

<input   name="bale"    id="bale"   >

<strong>Bales of </strong>
<input   name="balemade"    id="balemade"   >



<button type="button"   name="" onclick="calculateunitpacks()"  id="" class="btn btn-outline-info">Calculate</button>

<script>


function calculateunitpacks(){

var e = document.getElementById("unitId");
var valueE = e.options[e.selectedIndex].value;
var textE = e.options[e.selectedIndex].text;
var valueN = -9;

var valArray = { 

     @foreach($unittypes as $unittype)
       "{{ $unittype->name }}" : {{ $unittype->value }}, 
      @endforeach
     }

     for (var key in valArray) {

var rtnComp = textE.localeCompare(key);

if (rtnComp == 0) {
 
 

 valueN = valArray[key];
 //alert(' Unit Val: ' + valueN); 

 document.getElementById("unit").value = valueN;

}
}



  
 
  var unit = document.getElementById("unit").value;




 
  var pack = document.getElementById("bale").value ;
  var packmade = document.getElementById("balemade").value ;
 // var unit = document.getElementById("unit").value ;






  var calc =  pack * packmade /unit ; 

 


  final = document.getElementById("qnt").value = calc ;
  if(calc){

  final = document.getElementById("qnt").value = calc ;

}

 
    }

    function check(){ 

      var product = document.getElementById("productId").value;
    if(product == 0){
        
        alert('Please select product');
        event.preventDefault();
    } 

var qntty = document.getElementById("qnt").value;


if( qntty.length <=0) {

event.preventDefault();
alert('Please enter the quantity produced');


} 

    var jbcrd = document.getElementById("jobcarditem").value;
    if(jbcrd.length < 1){
        
        alert('Job card item id cannot be null');
        event.preventDefault();
    } 

  



}

</script>
<br>
<br>
<strong>Start Date:</strong>
<input type="date" name="prodDate" id="prodDate" value="<?php echo date('Y-m-d'); ?>">
<br>


<button type="submit"  onclick="check()" name="myButton" value="create" id="create" class="btn btn-outline-info">Save</button>
</body>

</form>
</html>