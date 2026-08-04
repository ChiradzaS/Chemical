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
<script src="js/script.js"></script>



<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
$(document).ready(function(){
 
// $("button").click(function(){
// $("p").slideToggle();
// alert("Hello")
// });

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

multiply(); 
discount();
vat();

}

</script>
</head>
<body>
<div>
{{-- @include('view') --}}
<br>
</div>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Update Invoice item</h2>
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
<form action="{{ route('invoice_items.update',$invoice_item->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')


<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>



<style>
    span.select2.select2-container.select2-container--classic{
        width: 100% !important;
    }
</style>
<table class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Invoice Id:</strong>
<input name="invoicesId"  class="form-control form-control-sm"   value="{{ $invoice_item->invoicesId }}" placeholder="Invoice Id" hidden>
@error('quantity')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<strong>Product :</strong>
<div class="col-xs-12 col-sm-12 col-md-12">
<select  id="productId" name="productId" class="js-example-basic-single"   onchange="multiply()">
@foreach($products as $product)
<option value="{{$product->id}}"  @if($product->id==$invoice_item->productId) selected @endif> {{$product->name}}</option>
@endforeach
</select>
</div>
</div>
<strong>Unit Type :</strong>
<div class="col-xs-12 col-sm-12 col-md-12">
<select id="unitId" name="unitId" class="form-control form-control-sm"    placeholder="-- Select unit Type --" >
@foreach($unittypes as $unittype)
<option value="{{$unittype->id}}"  @if($unittype->id==$invoice_item->unitId) selected @endif> {{$unittype->name}}</option>
@endforeach
</select>
</div>

<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Quantity:</strong>
<input  id="quantity" name="quantity" class="form-control form-control-sm"   value="{{ $invoice_item->quantity }}" placeholder="Quantity" onchange="multiply()">
@error('quantity')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Price</strong>
<input id="price" name="price" class="form-control form-control-sm"  value="{{ $invoice_item->price }}"  placeholder="price" onchange="multiply()" readonly>
@error('price')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Total Cost</strong>
<input id="totalPrice" name="totalPrice" class="form-control form-control-sm"  value="{{ $invoice_item->totalPrice }}" placeholder="totalPrice">
@error('totalPrice')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<script>
    function discount() {
        multiply(); 
        var totalcost = document.getElementById("totalPrice").value;
        var discount = document.getElementById("Discount").value;
        var discounted =  discount/100 * totalcost;
   
    
        var total =   totalcost - discounted ;

        document.getElementById("totalPrice").value = total;
       
      
     
     
        
     }
</script>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Discount</strong>
<input name="Discount" id="Discount" class="form-control form-control-sm" value="{{ $invoice_item->Discount }}" placeholder="0.00 %" onchange="discount()">
@error('totalPrice')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<script>
    function vat() {

        

        

        var totalcost = document.getElementById("totalPrice").value;
        var taxtype = document.getElementById("VatType").value;
        var comboBoxBagType = document.getElementById("VatType");
        var vatTypeValue = comboBoxBagType.value;
        var vatTypeText = comboBoxBagType.options[comboBoxBagType.selectedIndex].text;
        //alert('combo text'+vatTypeText );




        if(totalcost <= 0){
            alert("Please enter the quantity first");
            return;
        }
        else if( vatTypeText.trim()=='Standard VAT 15%')
        {

            var vatrate = totalcost * 0.15;
            
            var taxed = totalcost - vatrate ;

            document.getElementById("totalPrice").value = taxed ;
            vataamount = totalcost - taxed; 
            document.getElementById("vatAmnt").value = vataamount;
            

        }
        else if ( vatTypeText.trim()=='No VAT charged'){
            multiply();    
            discount();   
            document.getElementById("vatAmnt").value = 0,00;
        }
      
        
     }
</script>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>VatType</strong>
<select  id="VatType" name="VatType" class="js-example-basic-single"onchange="vat()" >
<option value="0"  >----- select VAT type -------</option>
@foreach($vattypes as $vattype)
<option value="{{$vattype->id}}"  @if($vattype->id==$invoice_item->VatType) selected @endif> {{$vattype->name}}</option>
@endforeach
</select>
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>VAT amnt</strong>
<input name="vatAmnt" id="vatAmnt" class="form-control form-control-sm" placeholder="VAT" value="{{ $invoice_item->vatAmnt}}" >
@error('vatAmnt')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>

<script>
    
    

   
    function preventFormSubmission()  {

  var taxtype = document.getElementById("VatType").value;
  var comboBoxBagType = document.getElementById("VatType");
  var vatTypeValue = comboBoxBagType.value;

  if(vatTypeValue==0){
      
      alert('Please select a VAT type ');
      event.preventDefault();
      //window.history.back();
  }

}


</script>



<button type="submit" class="btn btn-outline-info"  onclick="preventFormSubmission()">Save</button>
</body>
</html>