<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <title>Customer Prices</title>





<script type='text/javascript'>

 var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

 async function fetchConstants(unitId, materialType) {
   
    try {
        let response = await fetch("{{ route('getConstants') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": CSRF_TOKEN
            },
            body: JSON.stringify({ unitId, materialType })
        });

        let data = await response.json();
        //alert(JSON.stringify(data));
        return data;
    } catch (error) {
        console.error("Error fetching constants:", error);
    }
}


async function calculate() {
  
    var unitId       = document.getElementById('unitId').value;
    var materialType = document.getElementById('materialTypeId').value;
    var totalWidth   = document.getElementById('Twidth').value;
    var length       = document.getElementById('length').value;
    var micron       = document.getElementById('micron').value;

    var actualMicron = document.getElementById('actualMicron').value;
    var price = document.getElementById('price').value;

  

    // **Validation: Check if any input is empty**
    if (!unitId || !materialType || !totalWidth || !length || (!micron && !actualMicron) || !price) {
        alert("All fields must be filled. Please enter valid values.");
        return;
    }

    // Convert values to numbers
    totalWidth = parseFloat(totalWidth);
    length = parseFloat(length);
    micron = parseFloat(micron) || 0;
    actualMicron = parseFloat(actualMicron) || 0;
    price = parseFloat(price);

    // Ensure numbers are greater than zero
    if (totalWidth <= 0 || length <= 0 || price <= 0 || (micron <= 0 && actualMicron <= 0)) {
        alert("Values must be greater than zero.");
        return;
    }

    var constants = await fetchConstants(unitId, materialType);
    if (!constants) {
        alert("Error fetching constants. Please try again.");
        return;
    }

    var unitValue = constants.unitValue;
    var constantValue = constants.constantValue;
    var weightPer1000 = 0;
    //var weightPer1000 = 0;

    let price1PerKg = null;
    let price2PerKg = null;

    if (micron > 0) {
        let weightPer1000 = ((totalWidth / 10) * (length / 10) * (micron / 1000)) / constantValue;
        let weightPerKg = (weightPer1000 / 1000) * unitValue;
        price1PerKg = price / weightPerKg;
    }

    if (actualMicron > 0) {
        let weightPer1000 = ((totalWidth / 10) * (length / 10) * (actualMicron / 1000)) / constantValue;
        let weightPerKg = (weightPer1000 / 1000) * unitValue;
        price2PerKg = price / weightPerKg;
    }

    var weightperproduct = ((totalWidth / 10) * (length / 10) * (actualMicron / 1000)) / constantValue;


    
    document.getElementById('priceperproduct').value = weightperproduct.toFixed(2);
    document.getElementById('price2').value = price2PerKg.toFixed(2);
    document.getElementById('pricePerKg').value =price1PerKg.toFixed(2);

   
}



 function deleteItem(itemId) {
    if (confirm("Are you sure you want to delete this item?")) {

        //alert('hoyooooooo');
        $.ajax({


url: "{{ route('deleteprice') }}",
type: 'post',
data: {_token: CSRF_TOKEN, itemId:itemId},
dataType: 'json',
success: function(response){

    try {


        Swal.fire({
        title: 'Product deleted....',
        icon: 'success',
        showConfirmButton: true,
        timer: 1000,
        position: 'center',
        confirmButtonText: 'OK'
    }).then(() => {
        location.reload(); 
    });


       
       
        
    } catch (error) {
      
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while processing the pricing. Please try again.',
            icon: 'error',
            showConfirmButton: true,
            position: 'center',
            confirmButtonText: 'OK'
        });
    }


   }


    });
    }
}


function handleMaterialTypeChange(selectElement) {


    let selectedValue = selectElement.value;
    

    $.ajax({


        url: "{{ route('getTypeValue') }}",
        type: 'post',
        data: {_token: CSRF_TOKEN, typeId: selectedValue},
        dataType: 'json',
        success: function(response){

                document.getElementById('priceperkg').value = response;


           }


            });


                   }




                   function additems(){

                   var customerId = document.getElementById('customer').value;


              

                   if (customerId == 0 ) {

                        alert('Please select a customer');
                        event.preventDefault(); //
                         return;
                              }

                   var width = document.getElementById('width').value ;
                   var gusset = document.getElementById('gusset').value ;
                   var totalWidth = document.getElementById('Twidth').value;
                   var length = document.getElementById('length').value ;
                   var micron = document.getElementById('micron').value ;
                   var material = document.getElementById('materialTypeId').value ;
                   var colourId = document.getElementById('colour').value ;
                   var price2 = document.getElementById('price2').value ;
                   
                   var actualMicron = document.getElementById('actualMicron').value ;


                   //alert('microne'+actialMicron);
                   
                   if (colourId == 0 ) {

                        alert('Please select a colour');
                        event.preventDefault(); //
                        return;
                            }

                   var bagType = document.getElementById('bagType').value ;
                   
                   if (bagType == 0 ) {

                    alert('Please select a bag type');
                    event.preventDefault(); //
                    return;
                        }
                   var pricePerKg = document.getElementById('priceperkg').value;
                   var pricePer1000 = document.getElementById('priceperproduct').value ;
                   var price = document.getElementById('price').value;
                   var unitId = document.getElementById('unitId').value ;
         



                   $.ajax({


url: "{{ route('saveprice') }}",
type: 'post',
data: {_token: CSRF_TOKEN, customerId:customerId,width:width,gusset:gusset,totalWidth:totalWidth,length:length,micron:micron,material:material,colourId:colourId,bagType:bagType,pricePerKg:pricePerKg,pricePer1000:pricePer1000,actualMicron:actualMicron,price:price,unitId:unitId,price2:price2},
dataType: 'json',
success: function(response){

    try {


        Swal.fire({
        title: 'Product added....',
        icon: 'success',
        showConfirmButton: true,
        timer: 1000,
        position: 'center',
        confirmButtonText: 'OK'
    }).then(() => {
        location.reload();  // Reload the page after the alert
    });


        //window.reload();
        
        // Additional success handling if needed
       
        
    } catch (error) {
      
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while processing the pricing. Please try again.',
            icon: 'error',
            showConfirmButton: true,
            position: 'center',
            confirmButtonText: 'OK'
        });
    }


   }


    });
                   }



</script>
</head>
<body>

<br>
<br>

<!-- <form action="{{ route('setprices.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return validate()">
@csrf -->
<form action="{{ route('setprices.update',$setPrice->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="container">



<div class="col-9">

    <div class="input-group">
    <span class="input-group-text"><strong>Customer</strong></span>
    <select name="customer" class="form-control form-control-lg"  id="customer"  required>
    <option value="0" disabled selected hidden>--customer--</option>
    @foreach( $customers as $customer)
    <option value="{{ $customer->id }}" @if ($customer->id == $setPrice->customerId) selected @endif>{{ $customer->name }}</option>
    @endforeach
    </select>   
    <br>

    <span class="input-group-text"><strong>Price</strong></span>

    <input  class="form-control form-control-lg" id="price" name="price" value="{{ $setPrice->price }}"   type="text" onclick="clearInput('price')"   placeholder="Enter price" required  >

  <select name="unitId"  id="unitId" class="form-control form-control-lg"     required>
    <option value="0" disabled selected hidden>-- package --</option>
  @foreach($unittypes as $unittype)
  <option value="{{ $unittype->id }}" @if ($unittype->id == $setPrice->unitId) selected @endif>{{ $unittype->name }}</option>


  @endforeach
  </select>
    
</div>
</div>



    

<hr>
<div class="row justify-content-between">
<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Width</h5></label>
    <div class="input-group">
        <input  class="form-control form-control-lg" id="width" name="width" value="{{$setPrice->width}}"   type="text" onclick="clearInput('width')" onchange="add('width')"  placeholder="Enter Width" required  >
        <span class="input-group-text">mm</span>
    </div>
</div>

<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Gusset <span style="font-family: Arial, sans-serif;font-size: 14px;">( Optional )</span></h5>
</label>
    <div class="input-group">
        <input class="form-control form-control-lg" type="text" id="gusset" value="{{$setPrice->gusset}}" name="gusset" value="0" onclick="clearInput('gusset')" onchange="add('gusset')"  placeholder="Enter Gusset"   >
        <span class="input-group-text">mm</span>
    </div>
</div>


<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Total Width</h5></label>
    <div class="input-group">
        <input class="form-control form-control-lg" id="Twidth"  value="{{ ($setPrice->width ?? 0) + ($setPrice->gusset ?? 0) }}" type="text"  name="Twidth"   >
        
        <span class="input-group-text">mm</span>
    </div>
</div>
</div>




<div class="row justify-content-between">
<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Length</h5></label>
    <div class="input-group">
        <input  class="form-control form-control-lg" id="length" name="length" value="{{$setPrice->length}}"  onclick="clearInput('length')"  type="text" placeholder="Enter Length"   required>
        <span class="input-group-text">mm</span>
    </div>
</div>

<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Micron <span style="font-family: Arial, sans-serif;font-size: 14px;"><strong>[ Display mic | Actual mic ]</strong></span></h5></label> 
    <div class="input-group">
    <input  class="form-control form-control-lg" id="actualMicron" name="actualMicron" onclick="clearInput('actualMicron')" value="{{$setPrice->actualMicron}}"  type="text"   placeholder="Display Micron"  required>
        <input  class="form-control form-control-lg" id="micron" name="micron" onclick="clearInput('micron')" value="{{$setPrice->micron}}"  type="text"  placeholder="Real Micron"  required>

        <span class="input-group-text">micron</span>
    </div>
</div>



<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Material</h5></label>
    <div class="input-group">
    <select name="materialType" class="form-control form-control-lg"  id="materialTypeId"  required>
    <option value="0" disabled selected hidden>-- select material name --</option>
@foreach( $materialtypes as $materialtype)
<option value="{{ $materialtype->id }}" @if ($materialtype->id == $setPrice->material) selected @endif>{{ $materialtype->name }}</option>
@endforeach
</select>
    </div>
</div>
</div>
<br>
<br>

<div class="row justify-content-between">
<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Colour </h5></label>
    <div class="input-group">
    <select name="colour"  id="colour" class="form-control form-control-lg"     required>
    <option value="0" disabled selected hidden>-- colour --</option>
  @foreach($colourtypes as $colourtype)
  <option value="{{ $colourtype->id }}" @if ($colourtype->id == $setPrice->colourId) selected @endif>{{ $colourtype->name }}</option>


  @endforeach
     </select>
       
    </div>
</div>


<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Bag-Type</h5></label>
    <div class="input-group">
    <select  id="bagType" name="bagType" class="form-control form-control-lg"  placeholder="-- Select Bag Type --"   required>
    <option value="0" disabled selected hidden>-- select bag type --</option>
@foreach($bagtypes as $bagtype)
<option value="{{ $bagtype->id }}" @if ($bagtype->id == $setPrice->bagType) selected @endif>{{ $bagtype->name }}</option>


@endforeach
</select>
       
    </div>
</div>

<div class="col-4">
<label for="inputField" class="col-form-label"><h5>Weight/1000 <span style="font-family: Arial, sans-serif;font-size: 16px;"></span></h5>
</label>
    <div class="input-group">
        
    <input 
    class="form-control form-control-lg" 
    type="text" 
    id="priceperproduct" 
    name="priceperproduct" 
    value="{{ $setPrice->pricePer1000 }}"
 
>
        <span class="input-group-text">Weight/1000s</span>
     
    </div>
</div>
</div>
<br><br>
<div class="row justify-content-between">
<div class="col-3">

    <div class="input-group">
 
    <button type="submit" value="create" name="create" onclick="calculate()"  id="conditionalButton" class="btn btn-primary btn-lg" >Updates Product</button>

    

       
</div>
</div>


<div class="col-2">

    <div class="input-group">

    <button type="button" class="btn btn-primary btn-lg"   onclick="calculate()"   >Calculate </button>

    </div>
</div>

<style>
    #inputField {
        height: 80px; /* Adjust the height as needed */
    }

    .btn-thick {
    padding: 155px 30px; /* Adjust padding to increase thickness */
    font-weight: bold; /* Optionally, you can increase font weight for a bolder appearance */
      }
</style>

<div class="col-7">

    <div class="input-group">
    <input class="form-control form-control-lg" type="text" id="pricePerKg"  value="{{ $setPrice->pricePerKg }}" name="pricePerKg"   >
    <span class="input-group-text">Price/Real mic</span>
    <input class="form-control form-control-lg" type="text" id="price2"  value="{{ $setPrice->price2 }}" name="price2"  >
    <span class="input-group-text">Price/Display mic</span>
 
    </div>
</div>
</div>

 

 

  </div>
</div>  

</form>
<hr>
<button onclick="window.history.back()" class="btn btn-outline-secondary">
    <i  ></i> Back
</button>

<style>
    .back-btn:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.back-btn:active {
    transform: translateY(1px);
}
</style>


</body>
<script>

window.onload = function() {

    var width = document.getElementById(width).value;
    var gusset  = document.getElementById(gusset).value;

    var totalW = width + gusset;

    document.getElementById(Twidth).value = totalW;




    
};

function clearInput(inputId) {

                document.getElementById(inputId).value = '';
                
            }


                    
            function add(inputId) {


                var width = parseFloat(document.getElementById('width').value);
                var gusset = parseFloat(document.getElementById('gusset').value);

                // Check if width and gusset are valid numbers, otherwise set them to 0
                width = isNaN(width) ? 0 : width;
                gusset = isNaN(gusset) ? 0 : gusset;

                // Calculate totalWidth
                var totalWidth = width + gusset;

                // Update the value of the Twidth input
                document.getElementById('Twidth').value = totalWidth;
}





function validate() {



    var width      = parseFloat(document.getElementById('width').value);
    var micron     = parseFloat(document.getElementById('micron').value);
    var gusset     = parseFloat(document.getElementById('gusset').value);
    var Width      = parseFloat(document.getElementById('Twidth').value);
    var length     = parseFloat(document.getElementById('length').value);
    var priceperkg = parseFloat(document.getElementById('priceperkg').value);
    

  
   
    // For select input fields, get the selected value directly


    //alert('ui');

    var inputs = [
        { id: 'width', value: width },
        { id: 'Twidth', value: Width },
        { id: 'micron', value: micron },
        { id: 'length', value: length },
       
        
    ];

   

    var isValid = true;

    inputs.forEach(function(input) {
        if (isNaN(input.value) || (input.value <= 0 )) {
            isValid = false;
            alert('Please enter a valid number ' + input.id);
            event.preventDefault();
            validateForm();
            document.getElementById(input.id).classList.add('highlight');
            
        } else {
            document.getElementById(input.id).classList.remove('highlight');
        }
    });


    


    
}


function  validateForm() {

    ///alert('select');

    var bagType = document.getElementById('bagType').value;
    var colour = document.getElementById('colour').value;
    var materialType = document.getElementById('materialTypeId').value;

    //alert(bagType);

    // Check validity of select input fields
    if (bagType === '0' || colour === '0' || materialType === '0') {

    alert('Please select options for bag type, colour, and material type.');
    document.getElementById('bagType').classList.add('highlight');
    document.getElementById('colour').classList.add('highlight');
    document.getElementById('materialType').classList.add('highlight');
    event.preventDefault();
} else {
    document.getElementById('bagType').classList.remove('highlight');
    document.getElementById('colour').classList.remove('highlight');
    document.getElementById('materialType').classList.remove('highlight');
}


}
        
    </script>
</html>