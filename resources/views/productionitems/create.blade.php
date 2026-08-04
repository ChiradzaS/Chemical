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
 // alert(" P No : /////////");
$("#productId").change(function(){

var cboProduct = document.getElementById("productId");
var aproductId = cboProduct.options[cboProduct.selectedIndex].value;

var productid = Number(aproductId);
  alert(" P No : "+productid);

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

function checkinfo(){

  var radioBtn = document.getElementById("radio");
  var jobcardinfo = document.getElementById("jobcarditemInfo").value;


if(!radioBtn.checked) {

    event.preventDefault();
    alert('Please make sure job card item id is checked');
} 

if( jobcardinfo.length <=0) {

event.preventDefault();
alert('Please enter the jobcarditem id before fetch');
} 




       //  document.getElementById("unit").value = valueN;
       




}


function check(){ 

var qntty = document.getElementById("qnt").value;


if( qntty.length <=0) {

event.preventDefault();
alert('Please enter the quantity produced');


} 

    var jbcrd = document.getElementById("jobcarditemId").value;
    if(jbcrd.length < 1){
        
        alert('Job card item id cannot be null');
        event.preventDefault();
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    span.select2.select2-container.select2-container--classic{
        width: 30% !important;
    }
</style>

<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}
</style>

<input name="productionId"    value="{{$productionitem->productionId}}"  hidden >





&nbsp;&nbsp;<input type="radio" id="barcode" name="fetchId" value="barcode">
  <label for="barcode">Barcode</label> 
<br>
  <input type="radio" id="radio" name="fetchId" value="jobcarditemId" checked="checked">
  <label for="jobcarditemId">Job Card Item Id </label>   &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
<strong>Enter Jobcard Id and Fetch</strong>
  <input   name="jobcarditemInfo" id="jobcarditemInfo"   > &nbsp;
  <button type="submit" onclick="checkinfo()" name="myButton" value="fetchData"  class="btn btn-primary btn-sm"> &nbsp; &nbsp; &nbsp; &nbsp;Fetch &nbsp; &nbsp; &nbsp; &nbsp;</button>
  &nbsp; &nbsp;<strong> Outstanding </strong>
<input   name="jobcarditemQnt"   value="{{$jobcarditem->outstanding}}"  readonly >
<br>

<hr>
<br>
<strong> Job Card item Id</strong>
<input type="text"  name="jobcarditemId"  id="jobcarditemId" value="{{$productionitem->jobcarditemId}}"  readonly  > 




<strong>Product:</strong>
<select  id="productId" name="productId" class="js-example-basic-single" >
<option value="0"  >-- none --</option>
@foreach($porducts as $porduct)
<option value="{{ $porduct->id }}" @if($porduct->id==$productionitem->productId) selected @endif >{{ $porduct->name }}</option>
@endforeach
@error('productId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</select>
  
   

<strong>Unit</strong>
<select  id="unitId" name="unitId" class="js-example-basic-single" >
<option value="0"  >-- none --</option>
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}"  @if($unittype->id==$productionitem->unitId) selected @endif >{{ $unittype->name }} </option>
@endforeach
</select>
@error('unitId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror



<hr>
<br>

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

</script>



<strong> Quantity:</strong>
<input   name="qnt"    id="qnt"   readonly>


<strong>unit:</strong>
<input   name="unit"    id="unit"   readonly>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input   name="bale"    id="bale"   >

<strong>Bales of </strong>
<input   name="balemade"    id="balemade"   >



<button type="button"   name="" onclick="calculateunitpacks()"  id="" class="btn btn-primary btn-sm">Calculate</button>

<hr>
<br>
<strong>Date</strong>
<input type="date" id="prodDate" name="prodDate" value="<?php echo date('Y-m-d'); ?>">



<input name="weight"  hidden><br>

<br>

<script>
 
function validate(){

}
</script>
<button type="submit"  onclick="check()" name="myButton" value="create" id="create" class="btn btn-outline-info">Save</button>
</form>
</body>
</html>