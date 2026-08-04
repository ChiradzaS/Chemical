<!DOCTYPE html>
<html lang="en">
<title>Job card create  </title>

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

var cboProduct = document.getElementById("productId");
var aproductId = cboProduct.options[cboProduct.selectedIndex].value;

var productid = Number(aproductId);
//alert(" P No : "+productid);

if(productid > 0){
// alert("> 0");

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
for(var i=0; i<len; i++){
var id = response['data'][i].id;
var name = response['data'][i].name;
var unitTypeId = response['data'][i].unitTypeId;
var WeightPerProduct = response['data'][i].WeightPerProduct;
var bagType = response['data'][i].bagType;
var thickness = response['data'][i].thickness;
var totalWidth = response['data'][i].totalWidth;
var product_length = response['data'][i].product_length;
var color = response['data'][i].color;
var materialTypeId = response['data'][i].materialTypeId;
var product_Width = response['data'][i].product_Width;
var workInProgress = response['data'][i].workInProgressId;
var avgWeightPerProduct = response['data'][i].avgWeightPerProduct;
var gussetWidth = response['data'][i].gussetWidth;


document.getElementById("avgWeightPerProduct").value = "" + avgWeightPerProduct ;
document.getElementById("workInProgressId").value = "" +  workInProgress;
document.getElementById("unitId").value = "" + unitTypeId;
document.getElementById("WeightPerProduct").value = "" + WeightPerProduct;
document.getElementById("bagType").value = "" + bagType;
document.getElementById("thickness").value = "" + thickness;
document.getElementById("totalWidth").value = "" + totalWidth;
document.getElementById("product_length").value = "" + product_length;
document.getElementById("color").value = "" + color;
document.getElementById("materialTypeId").value = "" + materialTypeId;
document.getElementById("product_Width").value = "" + product_Width;
document.getElementById("gussetWidth").value = "" + gussetWidth;





}
}

}

var valArray = { 
@foreach($unittypes as $unittype)
 "{{ $unittype->name }}" : {{ $unittype->value }} , 
@endforeach
}
   
</script>

<body>
<div>
{{-- @include('view') --}}
<br>
</div>
<div class="container mt-2">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
</div>
<h2> Create Job Card Per OrderItem</h2>
<br>
<div class="pull-right">
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('job_cards.store') }}" method="POST" enctype="multipart/form-data">
@csrf

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



<strong>Product:</strong>
<select  id="productId" name="productId"  class="js-example-basic-single" >
<option  >&nbsp&nbsp----Select Product---- </option>
@foreach($porducts as $porduct)
<option value="{{ $porduct->id }}" @if($produc==$porduct->id) selected @endif>{{ $porduct->name }}</option>
@endforeach
</select>
</div>

<strong>Unit:</strong>
<select name="unitId" id="unitId" class="js-example-basic-single" >
<option >-- select unit --</option>
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id}}" @if($unit==$unittype->id) selected @endif >{{ $unittype->name }}</option>
@endforeach
</select>
</div>
</div>

<strong>Quantity:</strong>
<input type="text" id="qnt" name="qnt" >

<strong>Total Quantity:</strong>
<input type="text" id="totalqnt" name="totalqnt"  ><br>

<strong>WeightPerProduct</strong>
<input type="text"  name="WeightPerProduct"  id="WeightPerProduct"  value={{$Weight}} >


<strong>order_item:</strong>
<input type="text" id="orderitem" name="orderitem" value={{$orderitem}} >



<strong>gussetWidth(mm)</strong>
<input type="text" name="gussetWidth"   id="gussetWidth" value={{$gusset}} >


<strong>Width(mm)</strong>
<input type="text" name="product_Width" id="product_Width" value={{$width}} >

<br>

<strong>Material Type:  </strong>
<select name="materialTypeId" id="materialTypeId" >
@foreach( $materialtypes as $materialtype)
<option value="{{  $materialtype->id }}" @if($material==$materialtype->id) selected @endif >{{ $materialtype->name }}</option>

@endforeach
</select>


<strong>Colour:</strong>
  <select name="color"  id="color"  placeholder="-- Select Color --" >
  @foreach($colourtypes as $colourtype)
  <option value="{{ $colourtype->id }}"  @if($color==$colourtype->id) selected @endif>{{ $colourtype->name }}</option>
 
  @endforeach
  </select>


 
  <strong> thickness(mic)</strong>
<input type="text" name="thickness"  id="thickness" value={{$mic}} >


<strong>totalWidth(mm)</strong>
<input type="text" name="totalWidth" id="totalWidth" value={{$Twidth}} >


<input type="hidden" name="workInProgressId" id="workInProgressId" value={{$workInProgressId}} >
<br>



<strong>Testing Weight:</strong>
<input type="text" name="test" id="test" ><br>

<strong>Customer:</strong>
<select  id="customerId" name="customerId"  class="js-example-basic-single" >
<option  >-- select customer --</option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}" @if($cust==$customer->id) selected @endif >{{ $customer->name }}</option>
@endforeach
</select>
</div>

<strong>Bag Type:</strong>
<select  id="bagType" name="bagType" class="js-example-basic-single">
<option >-- select bagType --</option>
@foreach($bagtypes as $bagtype)
<option  value="{{ $bagtype->id }}"  @if($bagType==$bagtype->id) selected @endif>{{ $bagtype->name }} </option>
@endforeach
</select>
</div> 

<strong>Description:</strong>
<input type="text" name="description"  readonly><br>

<input type="text" name="noOfProcesses"  hidden><br>

<input type="hidden" name="product_length"  id="product_length" >

<strong>Other:</strong>
<textarea name="other" id="other" ></textarea><br>


<script>
     function display() {
       
       var val = Date.now()
       const uniqueId = Math.random().toString(36).substr(2, 22);

       var nId = ""+uniqueId+val;
       var valT = nId.toString().substr(7,13);
      
       const myElement = document.getElementById("barcode");
       myElement.value = valT;

    }


    


   
    

    function calculate() {

        var e = document.getElementById("unitId");
        var valueE = e.options[e.selectedIndex].value;
        var textE = e.options[e.selectedIndex].text;
        var valueN = -9;

        for (var key in valArray) {
     
            var rtnComp = textE.localeCompare(key);
      
            if (rtnComp == 0) {
              
              var unitDivide = 1000 / valArray[key];

              valueN = valArray[key];
              //alert(' Unit Val: ' + valArray); 
              var weightPerProduct1000 = document.getElementById("WeightPerProduct").value;
              //alert('100000: ' + weightPerProduct1000); 
            
            }
        }
      
    
        var length = document.getElementById("product_length").value;
       
        var micron = document.getElementById("thickness").value;
       
        var width = document.getElementById("totalWidth").value;
      
        var weightPerProduct1000 = document.getElementById("WeightPerProduct").value;
        
        var product = document.getElementById("productId").value;
        var unit = document.getElementById("unitId").value;
        var qnt = document.getElementById("qnt").value;
        //var process = document.getElementById("processid").checked = true;
        
        var weightperQntIn1000 = -9;
        //alert(' Q: ' + qnt);
        if ( (qnt == '')) {
                alert('Please insert the quantity');
                return;
        } else {
            var totalQnt = valueN * qnt;
            //alert(' Total : '+  totalQnt);
            var total = document.getElementById('totalqnt').value = totalQnt ;

            var qntPer1000 = totalQnt / 1000;
            //alert('Avg'+  avgper1000 );
            
            //--------------------------------------------------------------------------------

            weightperQntIn1000 = qntPer1000 * weightPerProduct1000;
            //alert('Total Quantity' + weightper1000 );
            var  perc = 0.03 *  weightperQntIn1000 ;//percent increase

            var finalTotal = perc +  weightperQntIn1000 ;

            var finalTotalz = qnt *  weightPerProduct1000  ;
            

            //--------------------------------------------------------------------------------

            var testingweight =  micron * width / 5600;
            //testing weight;

           //----------------------------------------------------------------------------------
            var constVar = 5.325;
            var WeightPerRoll = ((width/10 * length/10 * micron/1000)/constVar) / 1000;

           //----------------------------------------------------------------------------------          
            var centerfold = weightPerProduct1000 * qnt;
            var  perc = 0.03 *  centerfold;
            var centerfoldcalc = perc + centerfold;
            //--------------------------------------------------------------------------------------------

            
        } 
       // alert(' 1 ');
        var processQnt = document.getElementById("processQnt").value;

        var today = new Date(); 
        var selectedDate = document.getElementById("startDate").value; 

        if (selectedDate === "") { 
        document.getElementById("startDate").value = today.toISOString().slice(0,10); 
        }

        var barcode = document.getElementById("barcode").value;

        if (barcode === "") { 
        alert('Please Generate a barcode for your Jobcard');
        return;
        }


        // var barcode = document.getElementById("barcode").value;

        // if (barcode === "") { 
        // alert('Please Generate a barcode for your Jobcard');
        // return;
        // }
        //alert(' 2 ');

        
                   

        
        for (let j = 1; j < processQnt+1; j++) {
           
           
            var hiddenValue = document.getElementById('hiddenid_'+j).value;

            var bagtype = document.getElementById('bagType').value;
           
            var comboBoxBagType = document.getElementById("bagType");
            var bagTypeValue = comboBoxBagType.value;
            var workinprogress = document.getElementById('workInProgressId').value;
            var bagTypeText = comboBoxBagType.options[comboBoxBagType.selectedIndex].text;
           
            let lengthStr = hiddenValue.length;
            let resultInd = hiddenValue.indexOf("_");
            var process = hiddenValue.substr(0,resultInd);
            var noOfProcess = hiddenValue.substr(resultInd+1,lengthStr);
            
            
           
         

             
            if(bagTypeText.trim() == 'Rolls') {
                document.getElementById('test').value = testingweight ;
                document.getElementById('totalqnt').value = totalQnt ;

                //alert('OOOOOOOOOOOOOOOO : ' + bagTypeText); 
                if(hiddenValue.includes('Extruding')) {
                   document.getElementById('productId_'+noOfProcess).value = workinprogress  ;
                   document.getElementById('qnt_'+noOfProcess).value = finalTotalz;
                   document.getElementById('processid_'+noOfProcess).checked = true;
                   document.getElementById('unitId_'+noOfProcess).value = 52;
                }
                else if(hiddenValue.includes('Bagging')) {
                    //document.getElementById('productId_'+noOfProcess).value = product ;
                   
                } 
                else if (hiddenValue.includes('Extruding')) {
                   // document.getElementById('productId_'+noOfProcess).value = product ;
                   
                }  
                else if (hiddenValue.includes('Printing')) {
                   // document.getElementById('productId_'+noOfProcess).value = product ;
                   
                }  
                else if (hiddenValue.includes('Packing')) {
                   //document.getElementById('productId_'+noOfProcess).value = product ;
                  
                }  

            }
            else if(bagTypeText.trim() == 'Centre Fold') {
                document.getElementById('test').value = testingweight ;
                document.getElementById('totalqnt').value = totalQnt ;

                //alert('OOOOOOOOOOOOOOOO : ' + bagTypeText); 
                if(hiddenValue.includes('Extruding')) {
                   document.getElementById('productId_'+noOfProcess).value = workinprogress ;
                   document.getElementById('qnt_'+noOfProcess).value = centerfoldcalc;
                   document.getElementById('processid_'+noOfProcess).checked = true;
                   document.getElementById('unitId_'+noOfProcess).value = 52;
                }
                else if(hiddenValue.includes('Bagging')) {
                   document.getElementById('productId_'+noOfProcess).value = product ;
                   document.getElementById('qnt_'+noOfProcess).value = qnt;
                   document.getElementById('processid_'+noOfProcess).checked = true;
                   document.getElementById('unitId_'+noOfProcess).value = unit;
                   
                } 
                else if (hiddenValue.includes('Extruding')) {
                   // document.getElementById('productId_'+noOfProcess).value = product ;
                   
                }  
                else if (hiddenValue.includes('Printing')) {
                   // document.getElementById('productId_'+noOfProcess).value = product ;
                   
                }  
                else if (hiddenValue.includes('Packing')) {
                   //document.getElementById('productId_'+noOfProcess).value = product ;
                  
                }  

            }
            else if(hiddenValue.includes('Bagging')) {
                document.getElementById('productId_'+noOfProcess).value = product ;
                document.getElementById('processid_'+noOfProcess).checked = true;
                document.getElementById('qnt_'+noOfProcess).value = qnt;
                document.getElementById('unitId_'+noOfProcess).value = unit ;
              
            }  
            else if (hiddenValue.includes('Extruding')) {
                document.getElementById('productId_'+noOfProcess).value = workinprogress;
                document.getElementById('qnt_'+noOfProcess).value = finalTotal;
                document.getElementById('processid_'+noOfProcess).checked = true;
                document.getElementById('unitId_'+noOfProcess).value = 52;
                document.getElementById('test').value = testingweight ;
                
            }  
            else if (hiddenValue.includes('Printing')) {
                document.getElementById('productId_'+noOfProcess).value = product ;
                    
            }  
            else if (hiddenValue.includes('Packing')) 
            {
                document.getElementById('productId_'+noOfProcess).value = product ;
                
            }  

        }
    }

</script>
<strong>Barcode:</strong>
<input type="text" id="barcode" name="barcode">
<!-- <a onclick="display()" class="blue">Barcode</a>&nbsp&nbsp&nbsp&nbsp -->
<button type="button" onclick="display()"  padding right=5px  class="btn btn-dark"> Barcode </button>&nbsp&nbsp&nbsp&nbsp
<button type="button" onclick="calculate()"  padding right=5px  class="btn btn-dark"> Calculate </button>&nbsp&nbsp&nbsp&nbsp
<br>


<br>

@php $count=0 @endphp

@foreach($processtypes as $processtype)

@php $count++ @endphp
<strong>Generate Product:</strong>
<select  name="productId_{{$count}}" id="productId_{{$count}}" >
<option >-- select Product --</option>
    @foreach ($porducts as $porduct) {
       <option value='{{$porduct->id}}'>{{$porduct->name}}</option>
    } 
    @endforeach
</select>
<input type='checkbox'  name='processid_{{$count}}' id='processid_{{$count}}' value='{{$processtype->id}}' >&nbsp;{{$processtype->name}}&nbsp;
<strong>Quantity:</strong> <input type='text' name='qnt_{{$count}}' id='qnt_{{$count}}'>
<input type="hidden" id='hiddenid_{{$count}}' value='{{$processtype->name}}_{{$count}}'>
<strong>Unit:</strong>
    <select name='unitId_{{$count}}' id='unitId_{{$count}}'>
    <option >-- select unit --</option>
    @foreach($unittypes as $unittype) {
       <option value='{{$unittype->id}}' >{{$unittype->name}}</option>
    }
    @endforeach
    </select>
    <br>
@endforeach
<input type="hidden" id='processQnt' name='processQnt' value='{{$count}}'>

@php
$processQnt = $count;
print "<input type='text' name='processQnt' id='processQnt' value='$count' hidden></input><br>";
@endphp





<strong>Enter Start Date:</strong>
<input type="date" name="startDate"   id="startDate"  ><br>
<button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Save</button> 
</div>
</form>
</body>
</html>