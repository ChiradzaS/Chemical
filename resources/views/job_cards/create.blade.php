<!DOCTYPE html>
<html lang="en">
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th  style="font-size: 28px;">Create Jobcards</th>

<br><br><br><br><br>

</tr>
</thead>
</table>


<script src="{{ asset('js/ajax.googleapis.com_ajax_libs_jquery_3.6.1_jquery.min.js') }}"></script>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Script CDN -->


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



$.ajax({

url: "{{ route('getProductbyid') }}",

type: 'post',

data: {_token: CSRF_TOKEN, productid: productid},

dataType: 'json',

success: function(response){

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
                 
                level = -9;
                level = response['packagingLevel'];
                

                if (level > 0) {
                  if (len > 0) {
                    for(var i=0; i<len; i++){
                      
                       var unitTypeId = response['data'][i].unitPackId;
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
                       
                       
                       document.getElementById("unitId").value = "" + unitTypeId;
                       document.getElementById("avgWeightPerProduct").value = "" + avgWeightPerProduct ;
                       document.getElementById("workInProgressId").value = "" +  workInProgress;
                       document.getElementById("WeightPerProduct").value = "" + WeightPerProduct;
                       document.getElementById("bagType").value = "" + bagType;
                       document.getElementById("thickness").value = "" + thickness;
                       document.getElementById("totalWidth").value = "" + totalWidth;
                       document.getElementById("product_length").value = "" + product_length;
                       document.getElementById("color").value = "" + color;
                       document.getElementById("materialTypeId").value = "" + materialTypeId;
                       document.getElementById("product_Width").value = "" + product_Width;
                       document.getElementById("gussetWidth").value = "" + gussetWidth;
                       document.getElementById("qnt").focus();
                       

                       
                       var image = response['data'][i].image_path;

                      

                       if(image){
                        document.getElementById('processid_'+2).checked = true;
                        alert('Please NOTE!!! This jobcard will have a printing process');


                       }
                       
                    }
                  }
                } else {
                  if (len > 0) {
                    for(var i=0; i<len; i++){

                       
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
                      
                       
                       
                       document.getElementById("unitId").value = "" + unitTypeId;
                       document.getElementById("avgWeightPerProduct").value = "" + avgWeightPerProduct ;
                       document.getElementById("workInProgressId").value = "" +  workInProgress;
                       document.getElementById("WeightPerProduct").value = "" + WeightPerProduct;
                       document.getElementById("bagType").value = "" + bagType;
                       document.getElementById("thickness").value = "" + thickness;
                       document.getElementById("totalWidth").value = "" + totalWidth;
                       document.getElementById("product_length").value = "" + product_length;
                       document.getElementById("color").value = "" + color;
                       document.getElementById("materialTypeId").value = "" + materialTypeId;
                       document.getElementById("product_Width").value = "" + product_Width;
                       document.getElementById("gussetWidth").value = "" + gussetWidth;
                       document.getElementById("qnt").focus();

                       if(image){
                        document.getElementById('processid_'+2).checked = true;
                        alert('Please NOTE!!! This jobcard will have a printing process');


                       }
                      
                    }
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



<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>








 







<input type="hidden" name="orderId" id="orderId">


<input type="hidden" name="workInProgressId" id="workInProgressId">
<br>

<table class="table table-borderless">
  <thead>
    <tr>
      <th scope="col"><strong>Customer:</strong>&nbsp;
<select  id="customerId" name="customerId"  class="js-example-basic-single">
<option value="0" >&nbsp&nbsp----Select Customer---- </option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}" >{{ $customer->name }}</option>
@endforeach
</select></th>
<td>


<button type="button" class="button-20"  onclick="calculate()" >&nbsp;&nbsp;&nbsp;&nbsp;Calculate&nbsp;&nbsp;&nbsp;</button>

</td>
      
    </tr>
  </thead>
 
</table>

<table class="table table-bordered">
  <thead>
    <tr>



<th scope="row"><strong>Product</strong>&nbsp;
<select  id="productId" name="productId" onchange="productinfo()" class="js-example-basic-single" >
<option value="0" >&nbsp&nbsp----Select Product---- </option>
@foreach($products as $product)
<option value="{{ $product->id }}" >{{ $product->name }}</option>
@endforeach
</select></th>



      <td  style="width: 400px;">
        
      <strong>Package/bale</strong>&nbsp;
<select name="unitId" id="unitId" class="form-control-sm"  >
<option >-- select unit --</option>
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id}}">{{ $unittype->name }}</option>
@endforeach
</select></td>









      <td><strong>Qnt (no of bales)</strong>&nbsp;&nbsp;&nbsp;
      <input type="text" id="qnt" name="qnt"  class="form-control-sm"></td>
      <td><strong>Qnt per Unit</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="text" id="totalqnt" name="totalqnt" class="form-control-sm" > </td>
    </tr>
  </thead>
  <tbody>


    <tr>
  
      <td><strong>WeightPerProduct(kg's per 1000)</strong>&nbsp;
      <input type="text"  name="WeightPerProduct"  id="WeightPerProduct"   class="form-control-sm">
    
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

      <strong>Avg Weight/Product</strong>&nbsp;
      <input type="text"  name="avgWeightPerProduct"  id="avgWeightPerProduct"   class="form-control-sm">
  </td>




    


      <td>
      <strong>Width(mm)</strong>&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="text" name="product_Width" id="product_Width"  class="form-control-sm">
      </td>


      <td><strong>gussetWidth(mm)</strong>&nbsp;
      <input type="text" name="gussetWidth"   id="gussetWidth"  class="form-control-sm"></td>

        <td><strong>totalWidth(mm)</strong>&nbsp;
      <input type="text" name="totalWidth" id="totalWidth"  class="form-control-sm"></strong>
</td>  


    </tr>

      
<style>

#qnt,#totalqnt,#unitId,#WeightPerProduct,#product_Width,#gussetWidth,#totalWidth,#materialTypeId,#color,#thickness,#avgWeightPerProduct,#test,#bagType,#unitId_1,#unitId_2,#unitId_3,#qnt_1,#qnt_2,#qnt_3,#productId_1,#productId_2 ,#productId_3  {
    border: 2px solid #ccc; /* Border thickness and color */
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Shadow effect */
    border-radius: 4px; /* Optional: Rounds the corners */
    padding: 5px; /* Optional: Adds padding inside the input */
}


/* CSS */
.button-20 {
  appearance: button;
  background-color: #4D4AE8;
  background-image: linear-gradient(180deg, rgba(255, 255, 255, .15), rgba(255, 255, 255, 0));
  border: 1px solid #4D4AE8;
  border-radius: 1rem;
  box-shadow: rgba(255, 255, 255, 0.15) 0 1px 0 inset,rgba(46, 54, 80, 0.075) 0 1px 1px;
  box-sizing: border-box;
  color: #FFFFFF;
  cursor: pointer;
  display: inline-block;
  font-family: Inter,sans-serif;
  font-size: 1rem;
  font-weight: 500;
  line-height: 1.5;
  margin: 0;
  padding: .5rem 1rem;
  text-align: center;
  text-transform: none;
  transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  vertical-align: middle;
}

.button-20:focus:not(:focus-visible),
.button-20:focus {
  outline: 0;
}

.button-20:hover {
  background-color: #3733E5;
  border-color: #3733E5;
}

.button-20:focus {
  background-color: #413FC5;
  border-color: #3E3BBA;
  box-shadow: rgba(255, 255, 255, 0.15) 0 1px 0 inset, rgba(46, 54, 80, 0.075) 0 1px 1px, rgba(104, 101, 235, 0.5) 0 0 0 .2rem;
}

.button-20:active {
  background-color: #3E3BBA;
  background-image: none;
  border-color: #3A38AE;
  box-shadow: rgba(46, 54, 80, 0.125) 0 3px 5px inset;
}

.button-20:active:focus {
  box-shadow: rgba(46, 54, 80, 0.125) 0 3px 5px inset, rgba(104, 101, 235, 0.5) 0 0 0 .2rem;
}

.button-20:disabled {
  background-image: none;
  box-shadow: none;
  opacity: .65;
  pointer-events: none;
}


input[type="checkbox"] {
  width: 20px;
  height: 20px;
}




</style>
    <tr>
      <td><strong>Material Type:  </strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<select name="materialTypeId" id="materialTypeId"  class="form-control-sm" >
@foreach( $materialtypes as $materialtype)
<option value="{{  $materialtype->id }}"  >{{ $materialtype->name }}</option>
<option value="" disabled selected hidden>-- select material name --</option>
@endforeach
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<strong>Bag Type</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<select  id="bagType" name="bagType"  class="form-control-sm">
<option >-- select bagType --</option>
@foreach($bagtypes as $bagtype)
<option  value="{{ $bagtype->id }}"  >{{ $bagtype->name }} </option>
@endforeach
</select>
</td>
      <td>
<strong>Colour:</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <select name="color"  id="color"  placeholder="-- Select Color --"  class="form-control-sm">
  @foreach($colourtypes as $colourtype)
  <option value="{{ $colourtype->id }}"  >{{ $colourtype->name }}</option>
  <option value="" disabled selected hidden>-- select color name --</option>
  @endforeach
  </select></td>
      <td> 
  <strong> thickness(mic)</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" name="thickness"  id="thickness"  class="form-control-sm">
</td>
<td>


<strong>Testing Weight:</strong>&nbsp;&nbsp;&nbsp;
<input type="text" name="test" id="test"   class="form-control-sm" readonly>
</td>

  </tbody>
</table>







<input type="hidden" name="product_length"  id="product_length" >




</div>


</div> 



<input type="text" name="noOfProcesses"  hidden><br>



<strong>






<script>
     function display() {
       
       var val = Date.now()
       const uniqueId = Math.random().toString(36).substr(2, 22);

       var nId = ""+uniqueId+val;
       var valT = nId.toString().substr(7,11);
      
       const myElement = document.getElementById("barcode");
       myElement.value = valT;

    }


    


   
    

    function calculate() {

         display();
         showSpinner();

        var e = document.getElementById("unitId");
        var valueE = e.options[e.selectedIndex].value;
        var textE = e.options[e.selectedIndex].text;
        var valueN = -9;

        var lengths = document.getElementById("product_length").value;
        var widths = document.getElementById("totalWidth").value;
        var microns = document.getElementById("thickness").value;
        let bagtyp = 'roll';

        // var rollName = Math.floor(widths)+'mm'+' x '+Math.floor(microns)+'mic'+;
        // alert(''+rollName);



      


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

        var avgWeightPerProduct = document.getElementById("avgWeightPerProduct").value;
        
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
           // var  perc = 0.03 *  centerfold;
            //var centerfoldcalc = perc + centerfold;
            //--------------------------------------------------------------------------------------------

            
        } 
       
        var processQnt = document.getElementById("processQnt").value;

        var combo = document.getElementById("customerId");
        if(combo.selectedIndex <=0)
        {
        alert("Please select Customer");
        return;
        }
              


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

        var comboproduct = document.getElementById("productId");
        if(comboproduct.selectedIndex <=0)
        {
        alert("Please select Select the product");
        return;
        }


        
                   

        
        for (let j = 1; j < processQnt+1; j++) {
           
           
            var hiddenValue = document.getElementById('hiddenid_'+j).value;

            var bagtype = document.getElementById('bagType').value;

            var workinprogress = document.getElementById('workInProgressId').value;
           
            var comboBoxBagType = document.getElementById("bagType");
            var bagTypeValue = comboBoxBagType.value;
            
            
            var bagTypeText = comboBoxBagType.options[comboBoxBagType.selectedIndex].text;
           
            let lengthStr = hiddenValue.length;
            let resultInd = hiddenValue.indexOf("_");
            var process = hiddenValue.substr(0,resultInd);
            var noOfProcess = hiddenValue.substr(resultInd+1,lengthStr);
            
            
           
         

             
            if(bagTypeText.trim() == 'Rolls') {
                document.getElementById('test').value = testingweight ;
                document.getElementById('totalqnt').value = totalQnt ;

                
                if(hiddenValue.includes('Extruding')) {
                   document.getElementById('productId_'+noOfProcess).value = product ;
                   document.getElementById('qnt_'+noOfProcess).value = finalTotalz;
                   document.getElementById('processid_'+noOfProcess).checked = true;
                   document.getElementById('unitId_'+noOfProcess).value = 52;
                }
                else if(hiddenValue.includes('Bagging')) {
                    
                   
                } 
                else if (hiddenValue.includes('Extruding')) {
                   
                   
                }  
                else if (hiddenValue.includes('Printing')) {
                   
                   
                }  
                else if (hiddenValue.includes('Packing')) {
                
                  
                }  

            }
            else if(bagTypeText.trim() == 'Centre Fold') {
                document.getElementById('test').value = testingweight ;
                document.getElementById('totalqnt').value = totalQnt ;

                 
                if(hiddenValue.includes('Extruding')) {

                   //document.getElementById('productId_'+noOfProcess).value = workinprogress ;
                   document.getElementById('productId_'+noOfProcess).value = product ;
                   document.getElementById('qnt_'+noOfProcess).value = centerfold ;
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
                document.getElementById('productId_'+noOfProcess).value = product;
                document.getElementById('processid_'+noOfProcess).checked = true;
                document.getElementById('qnt_'+noOfProcess).value = qnt;
                document.getElementById('unitId_'+noOfProcess).value = unit ;
              
            }  
            else if (hiddenValue.includes('Extruding')) {

                // var combo_box = document.getElementById('productId_'+noOfProcess);
                // combo_box.innerHTML = "";

                // var option1 = document.createElement("option");
                // option1.text = "Option 1";
                // combo_box.appendChild(option1);

                document.getElementById('productId_'+noOfProcess).value =  workinprogress ;
                document.getElementById('qnt_'+noOfProcess).value = finalTotal;
                document.getElementById('processid_'+noOfProcess).checked = true;
                document.getElementById('unitId_'+noOfProcess).value = 52;
                document.getElementById('test').value = testingweight ;
                
            }  
            else if (hiddenValue.includes('Printing')) {
                document.getElementById('productId_'+noOfProcess).value =  workinprogress ;
                document.getElementById('qnt_'+noOfProcess).value = finalTotal;
                document.getElementById('unitId_'+noOfProcess).value = 52;
                document.getElementById('test').value = testingweight ;
                    
            }  
            else if (hiddenValue.includes('Packing')) 
            {
                document.getElementById('productId_'+noOfProcess).value = product ;
                
            }  

        }
    }

</script>

<input type="hidden" id="barcode" name="barcode">
<!-- <a onclick="display()" class="blue">Barcode</a>&nbsp&nbsp&nbsp&nbsp -->








@php $count=0 @endphp

@foreach($processtypes as $processtype)

@php $count++ @endphp
<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col" style="width: 250px;"><input type='checkbox'  name='processid_{{$count}}' id='processid_{{$count}}' value='{{$processtype->id}}' >&nbsp;{{$processtype->name}}&nbsp;</th>
      <th scope="col"><strong>Product </strong>
<select  name="productId_{{$count}}" id="productId_{{$count}}" class="form-control-sm" readonly>
<option >-- select Product --</option>
    @foreach ($porducts as $porduct) {
       <option value='{{$porduct->id}}'>{{$porduct->name}}</option>
    } 
    @endforeach
</select></th>
      <th scope="col"><strong>Quantity</strong> <input type='text' name='qnt_{{$count}}' id='qnt_{{$count}}' class="form-control-sm" readonly>
      <input type="hidden" id='hiddenid_{{$count}}'  value='{{$processtype->name}}_{{$count}}'></th>
      <th scope="col"><strong>Unit</strong>
    <select name='unitId_{{$count}}' id='unitId_{{$count}}' class="form-control-sm" readonly>
    <option >-- select unit --</option>
    @foreach($unittypes as $unittype) {
       <option value='{{$unittype->id}}' >{{$unittype->name}}</option>
    }
    @endforeach
    </select></th>
    </tr>
  </thead>

</table>




   
@endforeach
<input type="hidden" id='processQnt' name='processQnt' value='{{$count}}'>

@php
$processQnt = $count;
print "<input type='text' name='processQnt' id='processQnt' value='$count' hidden></input><br>";
@endphp



<script type="text/javascript">

function my_code(){

const urlParams = new URLSearchParams(window.location.search);

var zero = 0;
document.getElementById("productId").value = zero
document.getElementById("customerId").value = zero



var productid = urlParams.get('value');

//alert(productid);

if( productid == null){
    return;
    
}

var customerId = urlParams.get('value1');
var qnt = urlParams.get('value2');
var orderId = urlParams.get('value3');





document.getElementById("orderId").value = orderId
document.getElementById("productId").value = productid
document.getElementById("customerId").value = customerId
document.getElementById("qnt").value = qnt
display() 



if(productid > 0){



$.ajax({
    
url: "{{ route('getProductbyid') }}",

type: 'post',

data: {_token: CSRF_TOKEN, productid: productid},

dataType: 'json',

success: function(response){


  
  if(response['data'] != null){

len = response['data'].length;
}

if (len > 0) {


for(var i=0; i<len; i++){
  

  var unitTypeId = response['data'][i].unitPackId;
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
  var product = response['data'][i].id
  
  
  document.getElementById("productId").value = productid
  document.getElementById("unitId").value = "" + unitTypeId;
  document.getElementById("avgWeightPerProduct").value = "" + avgWeightPerProduct ;
  document.getElementById("workInProgressId").value = "" +  workInProgress;
  document.getElementById("WeightPerProduct").value = "" + WeightPerProduct;
  document.getElementById("bagType").value = "" + bagType;
  document.getElementById("thickness").value = "" + thickness;
  document.getElementById("totalWidth").value = "" + totalWidth;
  document.getElementById("product_length").value = "" + product_length;
  document.getElementById("color").value = "" + color;
  document.getElementById("materialTypeId").value = "" + materialTypeId;
  document.getElementById("product_Width").value = "" + product_Width;
  document.getElementById("gussetWidth").value = "" + gussetWidth;
  document.getElementById("qnt").focus();

  if(image){
                        document.getElementById('processid_'+2).checked = true;
                        alert('Please NOTE!!! This jobcard will have a printing process');


                       }
  
}
}

//setProductinfo(response);

}
});
}

};







var valArray = { 
@foreach($unittypes as $unittype)
 "{{ $unittype->name }}" : {{ $unittype->value }} , 
@endforeach
}



window.onload=my_code();
</script>


<input type="hidden" name="startDate" id="startDate">
<br>





    

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('image-preview');
                output.src = reader.result;
                output.style.display = 'block';
            }
            reader.readAsDataURL(event.target.files[0]);

        }

        
        function addPrint(){
          document.getElementById('processid_'+3).checked = true;
    }
    </script>

    <br>
    &nbsp;&nbsp;&nbsp;&nbsp;
<button type="submit" onclick="calculate()" padding-left=5px ; class="button-20"  >&nbsp;&nbsp;&nbsp;SAVE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</button> 
</div>
</form>


<style>
        /* Spinner Container */
        .spinner-container {
            display: none; /* Hidden by default */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        /* Spinner Style */
        .spinner {
            width: 17.6px;
            height: 17.6px;
            border-radius: 17.6px;
            box-shadow: 44px 0px 0 0 rgba(0, 0, 0, 0.2), 35.6px 26px 0 0 rgba(0, 0, 0, 0.4), 13.64px 41.8px 0 0 rgba(0, 0, 0, 0.6), -13.64px 41.8px 0 0 rgba(0, 0, 0, 0.8), -35.6px 26px 0 0 #000000;
            animation: spinner-b87k6z 1.4s infinite linear;
        }

        @keyframes spinner-b87k6z {
            to {
                transform: rotate(360deg);
            }
        }


    </style>
       

<!-- Spinner Container -->
<div id="spinnerContainer" class="spinner-container">
    <div class="spinner"></div>
</div>

<script>
    function showSpinner() {
        // Show the spinner
        document.getElementById('spinnerContainer').style.display = 'block';

        // Simulate a task (e.g., API call or delay)
        setTimeout(function () {
            // Hide the spinner after the task is complete
            document.getElementById('spinnerContainer').style.display = 'none';
        }, 1000); // 3 seconds delay for demonstration
    }
</script>
</body>
</html>