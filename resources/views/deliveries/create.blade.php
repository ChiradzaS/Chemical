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


<title>Add Deliveries Form - Laravel 8 CRUD</title>


<!-- Script CDN -->



<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<style>
    .centered {
  text-align: center;
}
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
<h2>Create Delivery / Invoice </h2>
</div>
<div class="pull-right">

</div>
</div>
</div>
@foreach ( $orderinfo as $orderinfo  )

<table class="table table-striped" >
<td class="highlights" width="400px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
  <span class="input-group-text" id="basic-addon1"> <strong>CUSTOMER</strong></span>
  </div>
  <select name="customerId" id="customerId" style="width:200px;"  class="form-control"  placeholder="-- Select Process --" disabled>
  <!-- <option value="-9"  class="centered">----------None---------- </option> -->
    @foreach($customers as $customer)
    <option value="{{ $customer->id }}" @if ($customer->id==$orderinfo['customerId']) selected @endif >{{ $customer->name }} </option>
    @endforeach

    </select>
</div>
</td>
@endforeach

<td class="highlights" width="250px" >
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1"><strong>Vehicle</strong></span>
  </div>

  <select name="vehicleReg" id="vehicleReg"   class="form-control" >
  <!-- <option value="-9"  class="centered">-----select Vehicle---- </option> -->
    @foreach($vehicles as $vehicle)
    <option value="{{$vehicle->id}}" >{{ $vehicle->name }} </option>
    @endforeach

    </select>
</div>
</td>
<td class="highlights" width="250px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1" ><strong>Driver</strong></span>
  </div>
  

  <select name="driver"  id="driver" class="form-control">
  <!-- <option value="-9"  class="centered">-----Select Driver-----</option> -->
    @foreach($drivers as $driver)
    <option value="{{$driver->id}}" >{{ $driver->name }} </option>
    @endforeach

    </select>
</div>
</td>
<td class="highlights" width="400px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1"><strong>Address</strong></span>
  </div>
  <input type="text" name="addressId"  value="13 smith street " id="addressId" class="form-control">
</div>
</td>


</form>
<br>
</div>
</div>
</div>



<table class="table table-striped" >
    <tr>
    <!-- <th  class="highlights">  <select  id="VatType" value="VatType" style="width: 110px" style="length: 110px"class="large" onchange="selectall()" >
        <option value="0" class="centered" >--NO VAT --</option>
        @foreach($vattypes as $vattype)
        <option value="{{$vattype->value}}"  class="large" > {{$vattype->name}}</option>
        @endforeach
        </select></th> -->
        <script>
            function myFunction() {
  
  var checkBox = document.getElementById("invoice");
  
  var text = document.getElementById("text");

  
  if (checkBox.checked == true){
    text.style.display = "block";
  } else {
    text.style.display = "none";
  }
}
        </script>
    <th   class="highlights"><a class="btn btn-dark" id="myLink" href="{{ route('deliveries.index') }}">Delivery List</a></th>
    <th   class="highlights"></th>
    <th   class="highlights"></th>
    <th   class="highlights"></th>
    <th   class="highlights"></th>
    <th   class="highlights"></th>
    <th   class="highlights"></th>
    <th   class="highlights"></th>
    <th   class="highlights"><p id="text" style="display:none">Generate INVOICE</p></th>
    <th   class="highlights"   width="300px"><label class="switch">
  <input type="checkbox" id="invoice" name="invoice" onclick="myFunction()">
  <span class="slider round"></span>
</label></th>
    </tr>

    <tr>
    <th  scope="col" class="centered"> ID</th>
    <th  scope="col" class="centered"> Reference</th>
    <th  scope="col" class="centered"> Product</th>
    <th  scope="col" class="centered"> Pack</th>
    <th  scope="col" class="centered"> Quantity</th>
    <th  scope="col" class="centered"> Price</th>
    <th  scope="col" class="centered"> Discount (%)</th>
    <th  scope="col" class="centered"> Vat (15%)</th>
    
   
    <th  scope="col" class="centered"> TOTAL</th>
    <th  scope="col" class="centered" >Select </th>
    </tr>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @foreach ($orderitems as $orderitem)


    <script type='text/javascript'>
     var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        
       

      function deliver(id,productId,customer,qnt,reference,unitId,vehicle,driver,address,price,vat,invoice,vatperitem,totalwithVat , responseData,prize,discount,uniqueNumber,total,vatperitem){

    

       
         var productid = id;
         var product = productId ;
         var squantity = qnt;
         var stopCharacter = "-";
         var stopIndex = squantity.indexOf(stopCharacter); 
         var quantity = squantity;

         if(quantity.length < 1){
            alert('Please enter the quantity');
            return;
         }
         var reference = reference;
         var unit = unitId;
         var vehicle = vehicle;
         var driver = driver;
         var address = address;
         var price = price;
         var prize = prize;

         var vat = vat;
         var invoice = invoice;
         //var qnt = qnt;
         
         
         //alert('azazazazaz'+vatperitem);


         var customer = customer;
         var vatperitem = vatperitem;
         //alert('vat '+);
         //var vatt = vatt;
         var totalwithVat = totalwithVat;
         //var totalwithoutVat = totalwithoutVat;
         //var discountTotal = discountTotal;
         //var discountper = discountper ;
        

   

        
        if( productid > 0){

            $.ajax({
            url: "{{ route('delivernote') }}",
            type: 'post',
            data: {_token: CSRF_TOKEN, productid:productid,product:product,customer:customer,quantity:quantity,reference:reference,unit:unit,vehicle:vehicle,driver:driver,address:address,price:price,vat:vat,invoice :invoice,vatperitem:vatperitem,totalwithVat:totalwithVat,prize:prize,discount:discount,uniqueNumber:uniqueNumber,total:total,vatperitem:vatperitem},
            dataType: 'json',
            success: function(response){

              runOnce(response);

        }
            
    
  
 
  })



        }





      }

      const runOnce = (function() {
  let hasRun = false;

  return function(response) {
    if (!hasRun) {
      const jsonData = encodeURIComponent(JSON.stringify(response));
      const linkURL = "{{ route('index.index') }}";
      const finalURL = linkURL + '?data=' + jsonData;
      window.open(finalURL, '_blank');
      var link = document.getElementById('myLink');
      link.click();
      hasRun = true;
    }
  };
})();

      function generateUniqueNumber() {
        const timestamp = new Date().getTime().toString(36);
        const randomStr = Math.random().toString(36).substring(2, 7); // Generate a random 5-character alphanumeric string
        return timestamp + randomStr;
        }

      </script>

      <script>

        
//function selectalldiscoun() {}
function selectall() 
{
    var totalInputs = document.querySelectorAll('.total-input');

    var sum = 0;

    totalInputs.forEach(function(input) {
    var value = parseFloat(input.value);
    if (!isNaN(value)) {
      sum += value;
      
    }
  });


  var totalElement = document.getElementById('Totalvalincl');
  var t = totalElement.textContent += sum;
 // alert(`The Total Value of the Order is `+sum);
  document.getElementById('Totalvalincl').value = sum;

  var totalincl = document.getElementById('Totalvalincl').value ;

  var tax = document.getElementById('Vat').value;

  var sum = Number(totalincl) + Number(tax);

  document.getElementById('Totalvalexcl').value = sum;



//////////////////////////////////////////////////////////////////////////


var totalvatInputs = document.querySelectorAll('.total-vat');

var sum = 0;

totalvatInputs.forEach(function(input) {
var value = parseFloat(input.value);
if (!isNaN(value)) {
  sum += value;
  
}
});


var totalvatElement = document.getElementById('Vat');
var t = totalvatElement.textContent += sum;
//alert(`The Total Value of the Order is `+sum);
document.getElementById('Vat').value = sum.toFixed(2);

var totalincl = document.getElementById('Totalvalincl').value;

var tax = document.getElementById('Vat').value;


var sum = Number(totalincl) + Number(tax);

document.getElementById('Totalvalexcl').value = sum ;


///////////////////////////////////////////////////////////////////////////

var totaldiscountInputs = document.querySelectorAll('.total-discount');

var sum = 0;

totaldiscountInputs.forEach(function(input) {
var value = parseFloat(input.value);
if (!isNaN(value)) {
  sum += value;
  
}
});


//alert('sum'+value);
 var totaldiscountElement = document.getElementById('discountTotal');
 var td = totaldiscountElement.textContent += sum;
 //alert(`Discount OOOOOOOOOOOOOOOO `+sum);
 document.getElementById('discountTotal').value = sum;



// document.getElementById('discountTotal').value = sum.toFixed(2) ;

    
}


function deliverytype() {


var invoice = document.getElementById("invoice");

//alert('invoce'+invoice);

if(invoice.checked){

  delivery();
  

}
else{

  noinoive();
 
 

}



}

     

    function delivery() {

      var invoice = document.getElementById("invoice");
      var customer = document.getElementById("customerId").value
      var uniqueNumber = generateUniqueNumber();
       
      
       
   

     

      $.ajax({
            url: "{{ route('invoice') }}",
            type: 'post',
            data: {_token: CSRF_TOKEN, customer:customer},
            dataType: 'json',
            success: function(response){

              var responseData = JSON.parse(response);
              var invoiceId = responseData;
              //alert(invoiceId );


              
  

      

       @foreach ($orderitems as $orderitem)
       
      
    
   //alert('wwwwwwwwwwwwwwwwwwwwwwwwwww'+ invoiceId);
      
 

       var qnt = document.getElementById("quantity-{{ $orderitem['id'] }}").value
      

       var prize  = document.getElementById("price-{{ $orderitem['id'] }}").value

       var qt  = document.getElementById("price-{{ $orderitem['id'] }}").value
       var separator = ", ";

       var price =  qt + separator + invoiceId;

       //alert('kfkfkkfkff'+price );

       var vat = document.getElementById("VatType-{{ $orderitem['id'] }}").value

       var total = document.getElementById("total-{{ $orderitem['id'] }}").value

        


       var vehicle = document.getElementById("vehicleReg").value

       var customer = document.getElementById("customerId").value

       var vatperitem = document.getElementById("vat-{{ $orderitem['id'] }}").value
       //alert('kfkfkkfkff'+vatperitem  );
       //var vatt = document.getElementById("Vat").value
      var totalwithVat = document.getElementById("Totalvalincl").value
       var totalwithoutVat = document.getElementById("Totalvalexcl").value
       var discountTotal = document.getElementById("discountTotal").value
       var discount = document.getElementById("discount-{{ $orderitem['id'] }}").value

      // alert('discount value'+discountper);

       if(vehicle.length < 1){
        alert('Please enter the Vehicle Registration Number')
        return;
       }

       var driver = document.getElementById("driver").value
       if(driver.length < 1){
        alert('Please enter driver name for delivery')
        return;
       }

       var address = document.getElementById("addressId").value
       if(address.length < 1){
        alert('Please enter address for delivery')
        return;
       }

       var box = document.getElementById("check-{{ $orderitem['id'] }}");

       var invoice = document.getElementById("invoice");

       //alert('invoce'+invoice);

       if(invoice.checked){

        var invoice = 100;
       }
       else{

        var invoice = 0;
        

       }

      
       
        if (box.checked) {
        

        deliver({{ $orderitem['id'] }},{{ $orderitem['productId']}},{{ $orderitem['customerId']}},qnt ,"{{ $orderitem['reference']}}",{{ $orderitem['unitId']}},vehicle,driver,address,price,vat,invoice,vatperitem,totalwithVat,customer,prize,discount,uniqueNumber,total,vatperitem);
       
        }
       

        @endforeach
      
      }})

     

    }

    function noinoive() {


      var uniqueNumber = generateUniqueNumber();
        




 @foreach ($orderitems as $orderitem)
 


//alert('wwwwwwwwwwwwwwwwwwwwwwwwwww'+ invoiceId);


var invoiceId = 0;

 var qnt = document.getElementById("quantity-{{ $orderitem['id'] }}").value


 var prize  = document.getElementById("price-{{ $orderitem['id'] }}").value

 var qt  = document.getElementById("price-{{ $orderitem['id']}}").value
 var separator = ", ";

 var price =  qt + separator + invoiceId;

 //alert('kfkfkkfkff'+price );

 var vat = 0;

 var vehicle = document.getElementById("vehicleReg").value

 var customer = document.getElementById("customerId").value
 var total = 0;
 var vatperitem =0;
 var vat= document.getElementById("Vat").value
 //var vatt = document.getElementById("Vat").value
var totalwithVat = document.getElementById("Totalvalincl").value
 var totalwithoutVat = document.getElementById("Totalvalexcl").value
 var discountTotal = document.getElementById("discountTotal").value
 var discount = document.getElementById("discount-{{ $orderitem['id'] }}").value

// alert('discount value'+discountper);

 if(vehicle.length < 1){
  alert('Please enter the Vehicle Registration Number')
  return;
 }

 var driver = document.getElementById("driver").value
 if(driver.length < 1){
  alert('Please enter driver name for delivery')
  return;
 }

 var address = document.getElementById("addressId").value
 if(address.length < 1){
  alert('Please enter address for delivery')
  return;
 }

 var box = document.getElementById("check-{{ $orderitem['id'] }}");

 var invoice = document.getElementById("invoice");

 //alert('invoce'+invoice);

 if(invoice.checked){

  var invoice = 100;
 }
 else{

  var invoice = 0;
  

 }


 
  if (box.checked) {
  

  deliver({{ $orderitem['id'] }},{{ $orderitem['productId']}},{{ $orderitem['customerId']}},qnt ,"{{ $orderitem['reference']}}",{{ $orderitem['unitId']}},vehicle,driver,address,price,vat,invoice,vatperitem,totalwithVat,customer,prize,discount,uniqueNumber,total,vatperitem);
 
  }
 

  @endforeach

}




function checkbox(id){


var qnt = document.getElementById("quantity-"+id).value
var total = document.getElementById("total-"+id).value
var total = document.getElementById("discount-"+id).value
let checkbox = document.getElementById("check-"+id);

checkbox.addEventListener("change", function() {
  if (!checkbox.checked) {

     document.getElementById("quantity-"+id).value = null;
     document.getElementById("total-"+id).value = null;
     document.getElementById("discountV-"+id).value = null;
     document.getElementById("discount-"+id).value = null;
     
     selectall();
     vat(id);
     checkbox.checked = false;
    

  }
})





}


function discount(id) {



var qnt = document.getElementById("quantity-"+id).value
var price = document.getElementById("price-"+id).value
var discount = document.getElementById("discount-"+id).value
var vat = document.getElementById("vat-"+id).value




var total =  qnt * price ;

var discountvalue = discount/100 * total ;


document.getElementById("discountV-"+id).value = discountvalue ;

//alert ('discount value is'+discountvalue );

var finaltotal =  Number(total)  - Number(discountvalue);

if(vat > 0){

var finaltotal = vat/100 * finaltotal ; 

}

document.getElementById("total-"+id).value = finaltotal;
//vat(id);
selectall();

if (!discount)
{ discount(id) }



}



function vat(id) {



var qnt = document.getElementById("quantity-"+id).value

  if(qnt){

    var checkbox = document.getElementById("check-"+id); 
    checkbox.checked = true;
  }else{
    var checkbox = document.getElementById("check-"+id); 
    checkbox.checked = false;
    alert ("Please select quantity");
    return;

  }

var discount = document.getElementById("discount-"+id).value

var discountV = document.getElementById("discountV-"+id).value

var price = document.getElementById("price-"+id).value

var vat = document.getElementById("VatType-"+id).value

var check = document.getElementById("check-"+id).value


//discount(id);


if (vat > 0) {


  
    var salesnovat = qnt * price - discountV;
  

    var vat = vat/100 * salesnovat ; 
    document.getElementById("vat-"+id).value =  vat ;

   

    var salesvat = salesnovat - vat ;
 

    document.getElementById("total-"+id).value =  salesvat ;

    
    selectall(); 
    
}
else{

    var salesnovat = qnt * price - discountV;
    //alert('without  vat '+salesnovat );

    document.getElementById("total-"+id).value =  salesnovat ;
    document.getElementById("vat-"+id).value =  0 ;
    

    
    selectall(); 

}



    // var checkbox = document.getElementById("check-"+id); 
    // checkbox.checked = true;


  




}


</script>
@endforeach

@foreach ($orderitems as $orderitem)
    @php $tmpUnit= $unittypes[$orderitem['unitId']]; @endphp
    @php $tmpProduct= $porducts[$orderitem['productId']]; @endphp

    <tr>
    
        <td class="centered">{{ $orderitem['id'] }}</td>
        <td class="centered">{{ $orderitem['reference'] }}</td>
        <td class="centered">{{ $tmpProduct->name }}</td>
        <td class="centered">{{ $tmpUnit->name }}</td>
        <td class="centered" width="150px"> <input type="input" class="form-control" class="large" onchange="vat({{ $orderitem['id'] }})" id="quantity-{{ $orderitem['id'] }}" name="quantity" ></td>
        <td class="centered" width="150px"> <input type="input" class="form-control" class="large" onchange="vat({{ $orderitem['id'] }})" id="price-{{ $orderitem['id'] }}" name="price"  ></td>

        <td class="centered" width="150px"> 

        <select  id="discount-{{ $orderitem['id'] }}" class="form-control" class="large" onchange="discount({{ $orderitem['id'] }})" >
        <option value="0"  class="centered" >-- select Vat --</option>
        @foreach($discounttypes as  $discounttype)
        <option value="{{$discounttype->value}}"  class="large" > {{$discounttype->name}}</option>
        @endforeach
        </select>
        </td>
        <td  class="centered" width="210px">
     
        <select  id="VatType-{{ $orderitem['id'] }}" class="form-control" class="large" onchange="vat({{ $orderitem['id'] }})" >
        <option value="0"  class="centered" >-- select Vat --</option>
        @foreach($vattypes as $vattype)
      
        <option value="{{$vattype->value}}"  class="large" > {{$vattype->name}}</option>
        @endforeach
        </select>
       

        </td>
        <input type="hidden"  class="large form-control total-discount"  id="discountV-{{ $orderitem['id'] }}" name="discount" >
       <input type="hidden"  class="large total-vat" id="vat-{{ $orderitem['id'] }}" name="vat" >
   
        <td class="centered" width="200px" ><input type="input"  class="large form-control total-input" id="total-{{ $orderitem['id'] }}" name="total" ></td>

        <td class="centered" width="100px" ><input type="checkbox" class="larger" id="check-{{$orderitem['id']}}"  onclick="checkbox({{ $orderitem['id'] }})" name="check-{{ $orderitem['id'] }}"  ></td>
    </tr>

    <input type="hidden" id="orderitemId" name="orderitemId" value="{{ $orderitem['id']}}">
    <input type="hidden" id="reference" name="reference" value="{{ $orderitem['reference'] }}">
    <input type="hidden" id="productId" name="productId" value="{{ $orderitem['productId']}}">
    <input type="hidden" id="unit" name="unit" value="{{ $orderitem['unitId']}}">
    <input type="hidden" id="quantity-{{ $orderitem['id']}}" name="quantity" >



    @endforeach

    <style>
 

  .highlights {
  background-color: #B0C4DE;
}


      input.larger {
        width: 40px;
        height: 40px;
      }

      input.large {
        width: 200px;
        height: 40px;
      }

      /* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: grey;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
  
</style>


    
<tr width="200px">
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col" ><strong>Sub Total (excl VAT)</strong></td>
    <td scope="col" class="centered"><input type="input"   class="form-control" class="large" id="Totalvalexcl" name="Totalvalexcl"  ></td>

    <td scope="col"></td>
</tr>

<tr width="200px">
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col" ><strong>VAT</strong></td>
    <td scope="col" class="centered"><input type="input"   class="form-control" class="large" id="Vat" name="Vat"  ></td>

    <td scope="col"></td>
</tr>

<tr width="200px">
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col" ><strong>Discount (%)</strong></td>
    <td scope="col" class="centered"><input type="input"   class="form-control" class="large" id="discountTotal" name="discountTotal"  ></td>

    <td scope="col"></td>
</tr>

<tr width="200px">
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col"></td>
    <td scope="col" ><strong>TOTAL ( incl VAT )</strong></td>
    <td scope="col" class="centered"><input type="input"   class="form-control" class="large" id="Totalvalincl" name="Totalvalincl" ></td>

    <td scope="col"></td>
</tr>

<style>
  
</style>

<tr width="200px">
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights"></td>
    <td class="highlights" class="centered" ><input type="button" onclick="deliverytype()" class="btn btn-dark" id="vehicle1" name="vehicle1" value="Generate Delivery Note" ></td>

</tr>

       


<script>
    
    function getParameterValue(parameterName) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(parameterName);
    }

    
    const orderItemId = getParameterValue('orderitem');
    const quantity = getParameterValue('qnt');

    document.getElementById("quantity-"+orderItemId).value = quantity;


    

</script>

<br>


</form>
</body>
</html>