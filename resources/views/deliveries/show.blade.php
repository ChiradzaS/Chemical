
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font=awesome.min.css" >
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.19.0/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script type='text/javascript'>



var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

     function delivery() {


     
    //alert('yooooo');
      var invoice= document.getElementById("invoice");


var customer = document.getElementById("customerId").value;

if(customer.length < 1){
  alert('Please enter driver name for delivery')
  return;

 }


 var driver = document.getElementById("driverId").value;
 alert(driver);
 if(driver.length < 1){
  alert('Please enter driver name for delivery')
  return;
 }


var vehicle = document.getElementById("vehicleId").value;
if (vehicle.trim() === "" || vehicle == 0) {
    alert("Please select a vehicle.");
    return;
}

//alert('hoyoo');
                   
      var uniqueNumber  = generateUniqueNumber();
    
       

      $.ajax({
            url: "{{ route('invoice') }}",
            type: 'post',
            data: {_token: CSRF_TOKEN, customer:customer},
            dataType: 'json',
            success: function(response){
             // alert('hoyooooo');

              var responseData = JSON.parse(response);
              var invoiceId = responseData;

              //alert(invoiceId);

       
      },    error: function(xhr, status, error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }})




    }


</script>




  <style>
 

 .highlights {
 background-color: #0a8af4;
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
 background-color: #cd0101;
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


<div>

  <table class="table table-striped" >


<td class="highlights" width="400px">
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><strong>CUSTOMER</strong></span>
        </div>
        <select id="customerId" name="customerId" style="width:200px;" class="form-control" placeholder="-- Select Process --">
            <option value="">------- Select Customer ----------</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>
</td>

<td class="highlights" width="250px" >
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1"><strong>Vehicle</strong></span>
  </div>

  <select id="vehicleId"  name="vehicleId"   class="form-control" >
  <option value="0" >-------select vehicle----------</option>
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
  

  <select  id="driverId"  name="driverId"  class="form-control">
  <option value="0" >-------select driver----------</option>
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




<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type='text/javascript'>
     var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

     function delivery() {


     

      var invoice= document.getElementById("invoice");


      var customer = document.getElementById("customerId").value;
if (!customer) {
    alert('Please enter driver name for delivery');
    return;
}

var driver = document.getElementById("driverId").value;
if (!driver) {
    alert('Please enter driver name for delivery');
    return;
}

var vehicle = document.getElementById("vehicleId").value;
if (!vehicle) {
    alert("Please select a vehicle.");
    return;
}

//alert('hoyoo');
                   
      var uniqueNumber  = generateUniqueNumber();
    
       

      $.ajax({
            url: "{{ route('invoice') }}",
            type: 'post',
            data: {_token: CSRF_TOKEN, customer:customer},
            dataType: 'json',
            success: function(response){
              //alert('hoyooooo');

              var responseData = JSON.parse(response);
              var invoiceId = responseData;
              var customerId =   document.getElementById('customerId').value;
              var vehicleId   =   document.getElementById('vehicleId').value;
              var addressId  =   document.getElementById('addressId').value;
              var driverId = document.getElementById('driverId').value ?? 147;





// Get invoice items
let orderItems = []; // Define array to store items

$('tr.shado').each(function(index, element) {
    let row = $(element);
    
    let item = {
    productId:      parseFloat(row.find('input[name="prod"]').val()) || 0,
    unitTypeId:     parseFloat(row.find('input[name="unit"]').val()) || 0,
    quantity:       parseFloat(row.find('input[name="quantity"]').val()) || 0,
    vat:            parseFloat(row.find('input[name="vat_amount"]').val()) || 0,
    discount:       parseFloat(row.find('input[name="discount_amount"]').val()) || 0,
    price:          parseFloat(row.find('input[name="price"]').val()) || 0,
    total:          parseFloat(row.find('input[name="total"]').val()) || 0,
    customerId: customerId ,
    driverId: driverId,
    vehicleId: vehicleId,
    addressId: addressId,
    invoiceId: invoiceId,
    uniqueNumber: uniqueNumber
};

    // Add item to array
    orderItems.push(item);
});

         // alert(JSON.stringify(orderItems, null, 2));
           // return;
          // Use console.log instead of alert for objects

          
// Send data to Laravel backend using AJAX
$.ajax({


    url: "{{ route('delivernote1') }}",
    method: 'POST',
    data: {
        items: orderItems,
        _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
    },
    success: function(response) {
      ///alert('hoyooooo');
                 runOnce(response);
    },
    error: function(xhr, status, error) {
        console.error('Error saving items:', error);
        // Handle error (e.g., show error message)
    }
});

     



       
    }})

     

    }





function delivery1() {

var invoice= document.getElementById("invoice");
var customer = document.getElementById("customerId").value;
if (!customer) {
alert('Please enter driver name for delivery');
return;
}

var driver = document.getElementById("driverId").value;
if (!driver) {
alert('Please enter driver name for delivery');
return;
}

var vehicle = document.getElementById("vehicleId").value;
if (!vehicle) {
alert("Please select a vehicle.");
return;
}


             
  var uniqueNumber    =   generateUniqueNumber();
  var customerId      =   document.getElementById('customerId').value;
  var vehicleId       =   document.getElementById('vehicleId').value;
  var addressId       =   document.getElementById('addressId').value;
  var driverId        =   document.getElementById('driverId').value ?? 147;





// Get invoice items
let orderItems = []; // Define array to store items

$('tr.shado').each(function(index, element) {
let row = $(element);

let item = {
productId:      parseFloat(row.find('input[name="prod"]').val()) || 0,
unitTypeId:     parseFloat(row.find('input[name="unit"]').val()) || 0,
quantity:       parseFloat(row.find('input[name="quantity"]').val()) || 0,
vat:            parseFloat(row.find('input[name="vat_amount"]').val()) || 0,
discount:       parseFloat(row.find('input[name="discount_amount"]').val()) || 0,
price:          parseFloat(row.find('input[name="price"]').val()) || 0,
total:          parseFloat(row.find('input[name="total"]').val()) || 0,
customerId: customerId ,
driverId: driverId,
vehicleId: vehicleId,
addressId: addressId,
invoiceId: 0,
uniqueNumber: uniqueNumber
};

// Add item to array
orderItems.push(item);
});

    //alert(JSON.stringify(orderItems, null, 2));
     // return;
    // Use console.log instead of alert for objects

    
// Send data to Laravel backend using AJAX
$.ajax({


url: "{{ route('delivernote1') }}",
method: 'POST',
data: {
  items: orderItems,
  _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
},
success: function(response) {
///alert('hoyooooo');
           runOnce(response);
},
error: function(xhr, status, error) {
  console.error('Error saving items:', error);
  // Handle error (e.g., show error message)
}
});





 
}




        
       

      function deliver(invoiceId, productId, unitTypeId, quantity, vat, discount, price, total, vat_amount, discount_amount,uniqueNumber){

         // alert('hoyooos');
         var productid = productId;
         var product = productId ;
         var squantity = qnt;
         var stopCharacter = "-";
         var stopIndex = squantity.indexOf(stopCharacter); 
         var quantity = squantity;

         if(quantity.length < 1){
           // alert('Please enter the quantity');
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
         var customer = customer;
         var vatperitem = vatperitem;
         var totalwithVat = totalwithVat;
         
        

   

        
        if( productid > 0){

            $.ajax({
            url: "{{ route('delivernote1') }}",
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


       
function deliverytype() {


//alert('hoyoo');
var invoice = document.getElementById("invoice");



if(invoice.checked){


  //alert('hoyoo');
  delivery();
  

}
else{

  delivery1();
 
 

}



}

// function generateUniqueNumber() {
//         const timestamp = new Date().getTime().toString(36);
//         const randomStr = Math.random().toString(36).substring(2, 7); // Generate a random 5-character alphanumeric string
//         return timestamp + randomStr;
//         }

     




    function noinoive() {



      var uniqueNumber = generateUniqueNumber();
        


}







  function addTotal(id){



  $(document).on('input change', 'input[name="quantity"], input[name="price"], select#vat, select#discount', function() {
  let row = $(this).closest('tr'); // Get the current row
  let quantity = parseFloat(row.find('input[name="quantity"]').val()) || 0;
  let price = parseFloat(row.find('input[name="price"]').val()) || 0;
  let vat = parseFloat(row.find('select#vat').val()) || 0;
  let discount = parseFloat(row.find('select#discount').val()) || 0;

  // Calculate total price before VAT & discount
  let totalPrice = price * quantity;

  // Calculate Discount amount (deducted)
  let discountAmount = (totalPrice * discount) / 100;

  // Calculate VAT amount (added)
  let vatAmount = ((totalPrice - discountAmount) * vat) / 100;

  // Final price after applying discount and adding VAT
  let finalPrice = totalPrice - discountAmount + vatAmount;

  // Update VAT and Discount amounts in their respective input fields
  row.find('input[name="vat_amount"]').val(vatAmount.toFixed(2));
  row.find('input[name="discount_amount"]').val(discountAmount.toFixed(2));
  // Update the total price in the row
  row.find('input[name="total"]').val(finalPrice.toFixed(2));


  updateGrandTotal();


});

function updateGrandTotal() {

  
  let grandTotal = 0;
  let totalDiscount = 0;
  let totalVat = 0;

  
  $('tr').each(function() {
    let total = parseFloat($(this).find('input[name="total"]').val()) || 0;
    let discount = parseFloat($(this).find('input[name="discount_amount"]').val()) || 0;
    let vat = parseFloat($(this).find('input[name="vat_amount"]').val()) || 0;

    grandTotal += total;
    totalDiscount += discount;
    totalVat += vat;
  });

 
  $('input[name="totalInclVat"]').val(grandTotal.toFixed(2));
  $('input[name="discount"]').val(totalDiscount.toFixed(2));
  $('input[name="vat"]').val(totalVat.toFixed(2));

}



function myFunction() {
  
  var checkBox = document.getElementById("invoice");
  
  var text = document.getElementById("text");

  
  if (checkBox.checked == true){
    text.style.display = "block";
  } else {
    text.style.display = "none";
  }
}

  }


</script>


<script>



$(document).ready(function(){



    $('.js-example-basic-single').select2({



        theme: "classic"



    });



});





</script>







<script type='text/javascript'>



var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');



$(document).ready(function(){







$("#productId").change(function(){



  //window.alert("Get User Id");







var cboProduct = document.getElementById("productId");



var aproductId = cboProduct.options[cboProduct.selectedIndex].value;







var productid = Number(aproductId);



//alert(" P No : "+productid);



 







if(productid > 0){



 //alert("> 0");







// AJAX POST request



$.ajax({



url: "{{ route('getProductbyidFor') }}",



type: 'post',



data: {_token: CSRF_TOKEN, productid: productid},



dataType: 'json',



success: function(response){





document.getElementById("unitType").value = "" +  response.data.unitTypeId;



document.getElementById("actualSellingPrice").value = "" +  response.data.price;















}



});



}



















});







});



















function addProduct() {











 



var product = document.getElementById("productId").value;



var quantity = '';



var unit = document.getElementById("unitType").value;



var price = document.getElementById("actualSellingPrice").value;



var subTotal = 0;







// if (!quantity || isNaN(quantity) ) {



//     alert("Please enter valid quantity ");



//     return;



// }

if (!unit || !price || !product) {



alert("Please select the Product you want to add to the list ");



return;



}



















var productSelect = document.getElementById("productId");



var selectedOption = productSelect.options[productSelect.selectedIndex];



var selectedTextp = selectedOption.text;



var selectedValuep = selectedOption.value;


var unitSelect = document.getElementById("unitType");





var selectedOptionu = unitSelect.options[unitSelect.selectedIndex];


var selectedTextu = selectedOptionu.text;







var selectedValueu = selectedOptionu.value;













  







 







var row = '<tr class="shado" style="width:40px" data-product-id="' + selectedValuep + '">' +



    //'<td style="display: none;">' + selectedValuep+ '</td>' +



    //'<td style="display: none;">' + quantity+ '</td>' +
    '<td  class="shado text-center "  style="display: none;" >' +
    '<input type="text" id="unit" name="unit"  class="form-control" value="' +  selectedValueu   + '">' +
    '</td>' +

    '<td  class="shado text-center "  style="display: none;" >' +
    '<input type="text" id="prod" name="prod"  class="form-control" value="' +  selectedValuep   + '">' +
    '</td>' +



    

  


    '<td  class="shado text-center "  style="text-align:center;" >' +
    '   <div class="input-group mb-3">' +
    '       <div class="input-group-prepend">' +
    '       </div>' +
    '      <input type="text" id="productId" name="productId" disabled style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" class="form-control" value="' +  selectedTextp  + '">' +
    '   </div>' +
    '</td>' +



    '<td width="200px" style="text-align: center;">' +
    '   <div class="input-group mb-3">' +
    '       <div class="input-group-prepend">' +
    '       </div>' +
    '       <input type="text" id="unitTypeId"  name="unitTypeId"   style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" disabled value="' + selectedTextu+ '" class="form-control">' +
    '   </div>' +
    '</td>' +




    '<td width="200px" style="text-align: center;">' +
    '   <div class="input-group mb-3">' +
    '       <input type="text" name="quantity" id="quantity" style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="quantity"  value="' + quantity+ '"  placeholder="Enter Quantity" class="form-control">' +
    '   </div>' +
    '</td>' +

    
'<td width="280px" style="text-align: center;">' +
    '<div class="input-group mb-3">' +
        '<div class="input-group-prepend">' +
            '<span class="input-group-text" id="basic-addon1">VAT(%)</span>' +
        '</div>' +
        '<select id="vat" class="form-control large" onchange="discount" style="border: none; background-color: white; font-size: 22px; color: black; outline: none;">' +
            '<option value="" class="centered">0 %</option>' +
            @foreach($vattypes as $vattype)
                '<option value="{{ $vattype->value }}" class="large">{{ $vattype->name }}</option>' +
            @endforeach
        '</select>' +
    '</div>' +
'</td>'+


'<td width="260px" style="text-align: center;">' +
    '<div class="input-group mb-3">' +
        '<div class="input-group-prepend">' +
            '<span class="input-group-text" id="basic-addon1">Discount(%)</span>' +
        '</div>' +
        '<select id="discount" class="form-control large" onchange="discount" style="border: none; background-color: white; font-size: 22px; color: black; outline: none;">' +
            '<option value="" class="centered">0%</option>' +
            @foreach($discounttypes as $discounttype)
                '<option value="{{ $discounttype->value }}" class="large">{{ $discounttype->name }}</option>' +
            @endforeach
        '</select>' +
    '</div>' +
'</td>'+











     '<td width="200px" style="text-align: center;">' +
     '   <div class="input-group mb-3">' +
     '       <div class="input-group-prepend">' +
     '           <span class="input-group-text" id="basic-addon1">Price</span>' +
     '       </div>' +
    '       <input type="text" name="price" id="price"   style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="price" value="' +  price  + '" class="form-control">' +
     '   </div>' +
     '</td>' +





     '<td width="200px" style="text-align: center;">' +
     '   <div class="input-group mb-3">' +
    //  '       <div class="input-group-prepend">' +
    //  '           <span class="input-group-text" id="basic-addon1">Total</span>' +
    //  '       </div>' +
    '       <input type="text" name="total" id="total"  style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="price"  class="form-control">' +
     '   </div>' +
     '</td>' +


     
     '<td width="200px" style="text-align: center;  display: none; ">' +
     '   <div class="input-group mb-3">' +
    //  '       <div class="input-group-prepend">' +
    //  '           <span class="input-group-text" id="basic-addon1">Total</span>' +
    //  '       </div>' +
    '       <input type="text" name="vat_amount" id="vat_amount"  style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="price"  class="form-control">' +
     '   </div>' +
     '</td>' +

     
     '<td width="200px" style="text-align: center;  display: none; ">' +
     '   <div class="input-group mb-3">' +
    //  '       <div class="input-group-prepend">' +
    //  '           <span class="input-group-text" id="basic-addon1">Total</span>' +
    //  '       </div>' +
    '       <input type="text" name="discount_amount" id="discount_amount"  style="border: none;  background-color: white; font-size: 22px; color: black; outline: none;" id="price"  class="form-control">' +
     '   </div>' +
     '</td>' +



  

     





     '<td  width="200px" style="text-align: center;">' +
    '   <div class="input-group mb-3">' +
    '       <a href="#" class="remove-row"><i class="fa fa-times" style="color: red; font-size: 40px; font-weight: bold;" aria-hidden="true"></i></a>' +
    '   </div>' +
    '</td>' +



  







 



    '</tr>';


     var id = 10;

    addTotal(id);

//----------------------------------------------------------------------------------------------------------------

var selectedTextp = selectedOption.text;// The selected value

var exists = false;

$('#table tr').each(function () {
    var rowValue = $(this).find('td.shado input').val(); // Adjust selector as needed

  
    if (rowValue === selectedTextp) {
        exists = true;
        return false; // Stop the loop if a match is found
    }
});

if (!exists) {
    $('#table').append(row);
} else {

    return;
}












    var completeOrderButton = document.getElementById("completeOrderButton");



    completeOrderButton.style.display = "block";











    Swal.fire({



    title: 'Product added to List',



    text: 'add',



    icon: 'success',



    confirmButtonText: 'OK',



    showConfirmButton: false, // Hide the "OK" button



    position: 'center',



    timer: 1000, // Auto-close the notification after 3 seconds (adjust as needed)



  }).then(() => {

    


    // Scroll to the bottom of the page



    //document.body.scrollTop = document.body.scrollHeight;







    



  });













}







function addorder() {



  var productsArray = []; // Declare the array outside the loop







  var quantities = document.querySelectorAll('input[name="quantity"]');



    var isValid = true;







    for (var i = 0; i < quantities.length; i++) {



    var quantity = quantities[i].value;



   







  



    if (!/^[1-9]\d*$/.test(quantity)) {



        isValid = false;



      



       



    }



}







     



      if (!isValid) {



          alert('Please make sure you have added Quantities for each product on the list');



          return false;



      }











  $('#table tr').each(function() {







    var productId = $(this).find('td:eq(0)').text(); 



    var pricePerBale = $(this).find('td:eq(1)').text();

    



    var qntty = $(this).find('td:eq(5) input').val();



    if (qntty == null || qntty === undefined) {



    qntty = 0; 



      }



      



 



    var productData = {



      productId: productId,



      quantity: qntty,



      pricePerBale: pricePerBale



    };











    productsArray.push(productData);



    //alert(JSON.stringify(productsArray));



  });







  // Move the alert outside the loop to display the array after the loop completes



  //alert(JSON.stringify(productsArray));







  







  $.ajax({



    url: "{{route('order')}}",



    method: 'POST',



    data: {_token: CSRF_TOKEN,productsArray: productsArray },



    success: function(response) {











 







      if(response==500){



    



        



          Swal.fire({



            title: 'Sorry Failed',



            text: 'Your Account has not been assigned to any Customer . Please contact admin ',



            icon: 'error',



            confirmButtonText: 'OK',



            showConfirmButton: false, 



            timer: 4000, 



          }).then(() => {



  



          });







          return false;







      }



      // Handle the response from the server if needed



      











  Swal.fire({



    title: 'Order Completed ',



    text: 'Thank you ',



    icon: 'success',



    confirmButtonText: 'OK',



    showConfirmButton: false, // Hide the "OK" button



    timer: 2000, // Auto-close the notification after 3 seconds (adjust as needed)



  }).then(() => {



    // Redirect to a specific page after the notification is closed



    document.getElementById('previousOrdersBtn').click();



  });















    },



    error: function(error) {



      alert('No items in order list . Please add...:', error);



    }



  });



}







$(document).on('click', '.remove-row', function() {



    $(this).closest('tr').remove();



});







</script>





<title>Create  Order </title>



<link rel="stylesheet"  href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >



</head>







<body>







<!-- Script CDN -->











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







     







<style>



  .combo-box {



  width: 500px; /* adjust the width as needed */



  /* other styling properties */



}







</style>



<table class="table table-striped" >



<td  colspan=2>



    <div class="input-group mb-3">







  <select  id="productId" name="productId" class="js-example-basic-single combo-box"   placeholder="-- Select Product --">



  <option value="" disabled selected hidden style="text-align:center;">-- select product name --</option>



@foreach($products as $product)



<option value="{{ $product->id }}" >{{ $product->name }}</option>



@endforeach



</select>



</div>



</td>



<td style="width: 16%;">



<div class="input-group mb-3">



  <div class="input-group-prepend">



    <span class="input-group-text" id="basic-addon1" >Package</span>



  </div>



  <select  id="unitType" name="unitType" disabled class="form-control"  >



  @foreach($unittypes as $unittype)



<option value="" disabled selected hidden>-Packaging-</option>



<option value="{{ $unittype->id }}" >{{ $unittype->name }}</option>







@endforeach



</div>







</td>







</td>



<td >


<!-- 
<div class="input-group mb-3">



  <div class="input-group-prepend">



    <span class="input-group-text" id="price" id="basic-addon1" disabled >Price</span>



  </div> -->



  <input type="text" name="price" id="actualSellingPrice" valueu='23' class="form-control" hidden >







<!-- </div> -->



</td>











<td >



<div class="input-group mb-3">





  



</div>















<td>



<button  class="btn btn-outline-success btn-lg" onclick="addProduct()"  type="submit">Add to List</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;



















<br>



</td><br>







<br>







</table>







  



 



<table class="  table table-striped"  >


<tr>

<th   class="highlights"><a class="btn btn-dark" id="myLink" href="{{ route('deliveries.index') }}">Delivery List</a></th>

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
<tr>


    <th  scope="col" class="centered"> Product</th>
    <th  scope="col" class="centered"> Pack</th>
    <th  scope="col" class="centered"> Quantity</th>
    <th  scope="col" class="centered"> Vat (15%)</th>
    <th  scope="col" class="centered"> Discount (%)</th>
    <th  scope="col" class="centered"> Price</th>
    <th  scope="col" class="centered"> TOTAL</th>
    <th  scope="col" class="centered" >Remove</th>
    </tr>








<tbody id="table" >







 </tbody>



 <tr>



 </tr>



</table>



<div class="card-footer text-right"> <!-- Add this div for the card footer -->



<table class="table table">

    <!-- <tr>
      <th scope="col" colspan='2' class=" centered"></th>
      <th scope="col" class=" centered"></th>
      <th scope="col" class=" centered"></th>
      <th scope="col" class=" centered"></th>
      <th scope="col" class="centered"></th>
      <th scope="col" class=" centered"></th>
      <th scope="col" class="centered"></th>
      <th scope="col" class=" centered"></th>
      <th scope="col">Sub Total Exc VAT</th>
      <td width="300px" style="text-align: center;">
      <div class="input-group mb-3">
           <input type="text" name="totalExVat" id="totalExVat"  style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="price"  class="form-control">
        </div>

     </td>
    </tr> -->

  <tbody>
    <tr>
      <th colspan='2'  class=" centered"></th>
      <td class="centered"></td>
      <td class="centered"></td>
      <td class="centered"></td>
      <th class="centered"></th>
      <td class="centered"></td>
      <th class="centered"></th>
      <td class="centered"></td>
      <th>VAT</th>
      <td width="300px" style="text-align: center;">
      <div class="input-group mb-3">
           <input type="text" name="vat" name="vat"  style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="price"  class="form-control">
        </div>

     </td>
    </tr>
</tbody>
<tbody>
    <tr>
      <th colspan='2'  class=" centered"></th>
      <td class="centered"></td>
      <td class="centered"></td>
      <td class="centered"></td>
      <th class="centered"></th>
      <td class="centered"></td>
      <th class="centered"></th>
      <td class="centered"></td>
      <th class="centered">Discount</th>
      <td width="300px" style="text-align: center;">
      <div class="input-group mb-3">
           <input type="text" name="discount" id="discount"   style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="price"  class="form-control">
        </div>

     </td>
 
    </tr>
</tbody>
<tbody>
    <tr>
      <th colspan='2'  class=" centered"></th>
      <td class="centered"></td>
      <td class="centered"></td>
      <td class="centered"></td>
      <th class="centered"></th>
      <td class="centered"></td>
      <th class="centered"></th>
      <td class="centered"></td>
      <th>Total (incl VAT)</th>
      <td width="300px" style="text-align: center;">
      <div class="input-group mb-3">
           <input type="text" name="totalInclVat" id="totalInclVat"  style="border: none; background-color: white; font-size: 22px; color: black; outline: none;" id="price"  class="form-control">
        </div>

     </td>
    </tr>
</tbody>


</table>

<table class="  table table-striped"  >




<tr>

    <th  scope="col" class="highlights centered"> </th>
    <th  scope="col" class="highlights centered"> </th>
    <th  scope="col" class="highlights centered"> </th>
    <th  scope="col" class="highlights centered"> </th>
    <th  scope="col" class="highlights centered"> </th>
    <th  scope="col" class="highlights centered"> </th>
    <th  scope="col" class="highlights centered"> </th>
    <td class="highlights" class="centered" ><input type="button" onclick="deliverytype()" class="btn btn-dark" id="vehicle1" name="vehicle1" value="Generate Delivery Note" ></td>

    </tr>



</table>
                </div>  



</body>



<script>








    </script>


</html>