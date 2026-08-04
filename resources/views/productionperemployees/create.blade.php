<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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



    <style>
        /* This styles the input tab */
        #myInput {
            background-color: red; /* Change this to the color you want */
        }
    </style>
<script>
  
        document.addEventListener("DOMContentLoaded", function() {

         //alert('welcome');

      var user = {{auth()->user()->id}};

      
$.ajax({
url: "{{ route('srchworkspace') }}",
type: 'post',
data: {_token: CSRF_TOKEN, user:user},
dataType: 'json',
success: function(response){

 
  
if(response > 0 ){

  //alert('woooooowoowowowowowoowow'+response);
  document.getElementById("productionId").value = response;
  var button = document.getElementById("disappear-button");
  button.style.display = "none"; //
}

// var disabledButton = document.querySelector(".btn-outline-danger[disabled]");
//     if (disabledButton) {
//         var productionItemId = disabledButton.getAttribute("data-value");
//         var state = disabledButton.getAttribute("data-state");

        
//         if (state != 1) {
//            // alert("Disabled button is the one to show - Production ID: " + productionItemId + ", State: " + state);
//             var button1 = document.getElementById("complete");
//             var button2 = document.getElementById("disappear-button");
//             document.getElementById('jobcardId').style.display = 'none';
//             document.getElementById('product').style.display = 'none';
//             document.getElementById('shift').style.display = 'none';
//             document.getElementById('machine').style.display = 'none';
//             button1.style.display = "none";
//             button2.style.display = "none";
          
          

            
           
//         }

// }




}


});




         var productionId = document.getElementById("productionId").value;
         //var button = document.getElementById("disappear-button");

         if(productionId  > 0){

          var button = document.getElementById("disappear-button");
          button.style.display = "none"; //

         }


 
        });
    </script>
<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

</head>

<script>
     document.addEventListener("DOMContentLoaded", function() {
    const myCombo = document.getElementById("machineryId");

    myCombo.addEventListener("change", function() {
      const selectedOption = myCombo.options[myCombo.selectedIndex];
        const selectedText = selectedOption.textContent;
        const firstWord = selectedText.split(' ')[0];
        //alert("Selected value: " + firstWord);

        document.getElementById("productionId").value;

        if( firstWord === 'Hover' || firstWord ==='Peforatted' || firstWord ==='Side' || firstWord ==='SO8' || firstWord ==='Carrier' || firstWord ==='Dropsheet' ){
         // alert('we bagging');
          document.getElementById("processId").value= 24;
        }

        else if ( firstWord === 'Extruder'){
          //alert('we extruding');
          document.getElementById("processId").value= 23;
        }

        else if( firstWord === 'Printer'){
         // alert('the printer is on');
          document.getElementById("processId").value= 84;
        }

         //alert('we extruding');

        const now = new Date(); 
        const hour = now.getHours(); 

      //  alert('we extruding');

        if (hour >= 18 || hour < 6) {
          document.getElementById("shiftId").value= 30;
        } else {
          document.getElementById("shiftId").value= 31;
        }

    });
});
</script>

<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

   


    function myFunction(){

      const myCombo = document.getElementById("machineryId").value;

      if (  myCombo  === '0'){
        alert('Please select Machine ');
        return false;
      }

      var process = document.getElementById("processId").value;
      //alert('hwhwhhw'+ process );

      var shift = document.getElementById("shiftId").value;
      //alert('Time'+ shift );

      var machine = document.getElementById("machineryId").value;
      //alert('Time'+ machine );

      var user = {{auth()->user()->id}};

      
        $.ajax({
        url: "{{ route('production') }}",
        type: 'post',
        data: {_token: CSRF_TOKEN, shift: shift , machine:machine ,process:process, user:user},
        dataType: 'json',
        success: function(response){

         
          


        // alert('woooooowoowowowowowoowow'+response);
          document.getElementById("productionId").value = response;
          var button = document.getElementById("disappear-button");
          button.style.display = "none"; //

         
        // hiddenTds.forEach(td => {
        //     if (td.style.display === "none") {
        //         td.style.display = "block"; // Change to desired display value
        //     } 
        // });
   
  

        }


        });

      


      

    
    }

    </script>


    <script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


    function Function(){


      var value = document.getElementById("qnt").value;

      if (isNaN(value)) {
    alert("Please enter a number");
    event.preventDefault(); // Move this line up
    return false;
}

var product = document.getElementById("prod").value;
//alert('Product1'+product);
if (!product){
        alert('Please search for product before you add production');
        event.preventDefault();  
      }
  
var unit = document.getElementById("unit").value;
//alert('Product2'+ unit );

if (!unit){
        alert('Please select the unit pack ');
        event.preventDefault();  
      }
      
var qnt = document.getElementById("qnt").value;
//alert('Product3'+qnt);

if (!qnt){
        alert('Please enter the quantity of Packs you made ');
        event.preventDefault();  
      }
var production = document.getElementById("productionId").value;
//alert('ProductQQQQQQQQQQQQQQQQQQ'+production);

if (!production){
        alert('Please add choose machine and add  Production ');
        event.preventDefault(); 
      }





}


    </script>

    <script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    
    function search(){

      var button = document.getElementById('disappear-button');

      if (button.style.display !== 'none') {
       
        alert('Please select machine and press th ADD button to proceed');
        location.reload();

    }

      var productId = document.getElementById("productionId").value;

      var machine = document.getElementById("machineryId").value;

      if ( productId === null ){
        alert('Please select machine and add to production ');
        return false;
      }

      

     


         //alert('yeyeyyee'+productId);

    // const myCombo = document.getElementById("machineryId").value;
    // const shift = document.getElementById("shiftId").value;

    //    if (  myCombo  === '0'){
    //     alert('Please select Machine ');
    //     return false;
    //   }


      var product = document.getElementById("productId").value;
      //alert('hwhwhhw'+ product );

      var jobcard = document.getElementById("jobcard").value;
      //alert('IOOOBCARD'+ jobcard );


      if (product > 1 && jobcard > 1) {

        alert("You can only search based on either the jobacard id OR by selecting the product ");
        location.reload();
                
            } else {

            }

      


      $.ajax({
        //alert('Jax' );
        url: "{{ route('searchproduction') }}",
        type: 'post',
        data: {_token:CSRF_TOKEN,product:product,jobcard:jobcard},
        dataType: 'json',
        success: function(response){
          

          //alert(JSON.stringify(response));
          //var jsonResponse = [{"id": 291}];
          var idValue = response[0].id;
          var unit = response[0].unitPackId;
          //alert(idValue);
          document.getElementById("prod").value = idValue ;
          document.getElementById("prod1").value = idValue ;
          document.getElementById("unit").value = unit ;
          document.getElementById("unit1").value = unit ;
         
          var cell = document.getElementById('tableToToggle');
          cell.style.display = 'table';

         
      
   
  

        }


        });

     

        


}
</script>
<body>
<div>

</div>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">

</div>

</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif


<br>

<script>

   function changestate(button) {


    alert('Are you sure you want to delete the item');
   
    var id = button.getAttribute("data-value");
   //alert('chnge'+id);
   //return;
    
    $.ajax({
      
        url: "{{ route('changestate') }}",
        type: 'post',
        data: {_token: CSRF_TOKEN, id:id },
        dataType: 'json',
        success: function(response){

          location.reload();

          //var row = button.parentNode.parentNode;
         


         //row.parentNode.removeChild(row);
  

        }


        });
  }



//   function changestat(button) {
   
//     var id = button.getAttribute('data-value');
//     //alert('chnge'+id);

    
//     var [productId, productionId] =id.split(',');

  
   
//     $.ajax({
//     url: "{{ route('changestat') }}",
//     type: 'POST',
//     data: {
//         _token: CSRF_TOKEN,
//         productId: productId,
//         productionId: productionId
//     },
//     dataType: 'json',
//     success: function(response) {

//         location.reload(); // For example, reload the page
//     },
//     error: function(xhr, status, error) {

//             alert('An unexpected error occurred. Please try again later.');
        
//     }
// });


//  }

//   function changestate(button) {
   
//     var row = button.parentNode.parentNode;


//      row.parentNode.removeChild(row);
// }

</script>
<div>
  
 <STRONG><p style="color:red">&nbsp;&nbsp;&nbspPLEASE NOTE !! Only use laravel after the shift when you go home ....</p></STRONG>
</div>
&nbsp;&nbsp;
<div class=".container mt-2">
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
           <h3>
            <strong>Production Details</strong></h3>
        </div>
        <div class="card-body">
          <table >
            <tbody>
       
  
      <td width="450" id="machine">

 
        <select class="custom-select mr-sm-2" id="processId"  name="processId" hidden>
          <option selected>Choose...</option>
          @foreach($processtypes as $processtype)
          <option value="{{ $processtype->id }}"   >{{ $processtype->name }} </option>
          @endforeach
        </select>
    
        
        

        <div class="input-group mb-3">
  <div class="input-group-prepend">
  <span class="input-group-text" id="basic-addon1"> <strong>Machine</strong></span>
  </div>
  <select class="custom-select mr-sm-2"  id="machineryId" name="machineryId"  class="form-control form-control-sm">
            <option value="0" selected>Choose...</option>
             @foreach($machinetypes as $machinetype)
            <option value="{{ $machinetype->id }}"  >{{ $machinetype->name }} </option>
           @endforeach
          </select>
</div>
          </td>

          <td width="350" id="shift">

     

          <div class="input-group mb-3">
  <div class="input-group-prepend">
  <span class="input-group-text" id="basic-addon1"> <strong>Shift</strong></span>
  </div>
  <select class="custom-select mr-sm-2" id="shiftId" name="shiftId"  class="form-control form-control-sm" >
                <option selected>Choose...</option>
                @foreach($shifttypes as $shifttype)
                <option value="{{ $shifttype->id }}"  >{{ $shifttype->name }} </option>
                @endforeach
              </select>
</div>
       


          
      
            <select class="custom-select mr-sm-2"  id="employeeId" name="employeeId" hidden>
              <option selected>Choose...</option>
              @foreach($employees as $employee)
               <option value="{{ $employee->id }}"  >{{ $employee->name }} </option>
               @endforeach
            </select>



        


        
            
             
          

<!--           
  <div class="form-row">
    <div class="form-group col-md-6">
    <strong class="mr-sm-2" for="inlineFormCustomSelect">Packer</strong>
     
      <input type="text" class="form-control" id="inputEmail4" placeholder="Other Employee" >
    </div>
  </div> -->




  </td>
  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;


<tr>
<td width="350">  <button type="button" id="disappear-button" onclick="myFunction()" class="btn btn-success">Add </button>

</tr>
      

</tr>

              
              <!-- Add more rows as needed -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
        <form action="{{ route('productionperemployees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <h3>
            <strong>Product / Job Card </strong></h3>
        </div>
        <div class="card-body">
          <table >
            <tbody>
              <tr>


           
<td id="jobcardId" >
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1" ><strong>Job Card</strong></span>
  </div>
  <input type="text" name="jobcard"  id="jobcard" class="form-control" onchange="search()">
</div>
</td>
    <tr>
    <td  style="width: 150px;" id="product">
       <select   name="productId" id="productId" class="js-example-basic-single "  onchange="search()">
      <option value="0" disabled selected hidden class="centered">---- Search by Product ----</option>
      @foreach($products as $product)
      <option value="{{ $product->id }}" >{{ $product->name }}</option>
      @endforeach
      </select></td>
    </tr>
    </td>
   
      <td class="hidden-td" ></td>
      <td class="colored-cell"></td>
    </tr>
            
             

             
            
              <!-- Add more rows as needed -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<br>
<table class="table table-striped"  >
  <!-- <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead> -->
  <tbody>

    

<input type="text" name="productionId"  value="{{$id}}" class="form-control form-control-sm" id="productionId" hidden>
    </tr>
    <tr>
     


    <table id="tableToToggle" style="display: none;" class="table table-striped table-dark centered" >
    <tr>


      



      <td >
       

      <div class="input-group mb-3">
  <div class="input-group-prepend">
  <span class="input-group-text" id="basic-addon1"> <strong>Product</strong></span>
  </div>
  <select   name="" id="prod" class="form-control " >
     
      @foreach($porducts as $porduct)
      <option value="{{ $porduct->id }}" disabled>{{ $porduct->name }}</option>
      @endforeach
      </select>
</div>

      
    </td>

    

      <td width="200px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
  <span class="input-group-text" id="basic-addon1"> <strong>Pack</strong></span>
  </div>
  <select  name="unit"  id="unit1" class="form-control " disabled >

@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}"    >{{ $unittype->name }}</option>

@endforeach
</select>
</div>

</td>
      <td  ><div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text" id="basic-addon1" id="myInput" placeholder="Quantity" ><strong>Quantity</strong></span>
  </div>

  <input type="hidden" name="productId"  id="prod1" class="form-control">

  <input type="hidden" name="unit"  id="unit" class="form-control">
  <input type="text" name="qnt"  id="qnt" class="form-control">
 

  <script>
     function reload() {
    location.reload(true);
};
  </script>

</div></td>
      <td  ><button type="submit"  onclick="Function()" class="btn btn-success">ADD production</button></td>
    </tr>

 </form>
 </table>
  </tbody>

  <br>               


<br>



</table>
<br>

<div class=".container mt-2">
  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
           <h3>
            <strong>Total Production</strong></h3>
        </div>
        <div class="card-body">
        <table class="table table-striped">
            <tbody>
              <tr>
              
              <th  scope="col"> Product</th>
              <th  scope="col"> PACK</th>
              <th  scope="col"> Quantity</th>
            
            
            
              </tr>

              @php

              use Carbon\Carbon;

              $value = auth()->user()->id;

              $currentDate = Carbon::now()->toDateString();

              $production = \App\Models\Production::where('userId', $value)
                                                   ->whereDate('created_at', $currentDate)
                                                   ->pluck('id');

               $productionitems = \App\Models\Productionitem::whereIn('productionId', $production)
                                                            ->where('stateId', '<>', 134)
                                                            ->where(function($query) {
                                                                $query->where('processId', '<>', 23)
                                                                      ->orWhereNull('processId');
                                                            })
                                                            ->get();



            


            $productionitemsx = \App\Models\Productionitem::whereIn('productionId', $production)
                                        ->where('stateId','<>', 134)
                                        ->where('processId', 23)
                                        ->orderBy('id','desc')->paginate(100);


            $summarizedItems = $productionitems->groupBy('productId')->map(function ($items) {
        return [
              'productionId' => $items->first()->productionId,
              'productId' => $items->first()->productId,
              'unitId' => $items->first()->unitId,
              'totalQuantity' => $items->sum('qnt'), 
                      ];
                  })->values();
                                                                              

                $workspaces = \App\Models\Workspace::where('userId', $value)
                                                    ->whereDate('created_at', $currentDate)
                                                    ->get();
                                          
            @endphp


<tbody>
    @if (count($summarizedItems) === 0)
        <tr>
            <td colspan="4">No production</td>
        </tr>
    @else
        @php
            $totalBaggingQuantity = 0; // Initialize total for Bagging
        @endphp

        <tr>
            <td colspan="4"><strong>Bagging</strong></td>
        </tr>
        @foreach ($summarizedItems as $item)
            @php
                $unitType = $unittypes[$item['unitId']]; // Assuming $unittypes is available
                $tmpProduct = $porducts[$item['productId']];
                $totalBaggingQuantity += $item['totalQuantity']; // Add to Bagging total
            @endphp
            <tr>
                <td>{{ $tmpProduct->name }}</td>
                <td>{{ $unitType->name }}</td>
                <td>{{ $item['totalQuantity'] }}</td>
            </tr>
        @endforeach

        {{-- Display the total for Bagging --}}
        <tr>
            <td colspan="3" class="large-td"><strong>Total Bales:</strong>  <strong>{{ $totalBaggingQuantity }} </strong></td> 
        </tr>
    @endif
</tbody>


</table>

                @foreach ($workspaces as $workspace)


                @php
                
                $state = $workspace->state;
                $productionId = $workspace->productionId;

                @endphp

              
                  
           
                @endforeach
               
              
               
            
              </tr>
        
              <!-- Add more rows as needed -->
            </tbody>
          </table>
        
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
        <form action="{{ route('productionperemployees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <h3>
            <strong>Production In detail </strong></h3>
        </div>
        <div class="card-body">
        <table class="table table-striped">
            <tbody>


              @php

              use Carbon\Carbon as CarbonInstance;

              $value = auth()->user()->id;

              $currentDate = Carbon::now()->toDateString();

              $production = \App\Models\Production::where('userId', $value)
                                                   ->whereDate('created_at', $currentDate)
                                                   ->pluck('id');

               $productionitems = \App\Models\Productionitem::whereIn('productionId', $production)
                                                            ->where('stateId', '<>', 134)
                                                            ->where(function($query) {
                                                                $query->where('processId', '<>', 23)
                                                                      ->orWhereNull('processId');
                                                            })
                                                            
                                                            ->get();



            


            $productionitemsx = \App\Models\Productionitem::whereIn('productionId', $production)
                                        ->where('stateId','<>', 134)
                                        ->where('processId', 23)
                                        ->orderBy('id','desc')->paginate(100);


            $summarizedItems = $productionitems->groupBy('productId')->map(function ($items) {
        return [
              'productionId' => $items->first()->productionId,
              'productId' => $items->first()->productId,
              'unitId' => $items->first()->unitId,
              'totalQuantity' => $items->sum('qnt'), 
                      ];
                  })->values();
                                                                              

                $workspaces = \App\Models\Workspace::where('userId', $value)
                                                    ->whereDate('created_at', $currentDate)
                                                    ->get();
                                          
            @endphp
         

    <tbody>
        @if (count($productionitems) === 0)
            <tr>
                <td colspan="4">No production</td>
            </tr>
        @else

            @foreach ($productionitems as $item)
                @php
                    $unitType = $unittypes[$item['unitId']]; // Assuming $unittypes is available
                    $tmpProduct = $porducts[$item['productId']];
                @endphp
                <ul>
    <li>
    <span style="margin-right: 30px;" class="no-print">
                                           
                                           <!-- <button type="button" class="btn btn-outline-danger btn-sm"  onclick="changestate(this)"   data-value="{{ $item['id'] }}"      >delete</button> -->
                                           <a href="javascript:void(0)" style="color:red" onclick="changestate(this)" data-value="{{ $item['id'] }}">
    Delete
</a>

   
   
                                           </span> &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;
                                           <span>
<strong> {{ $item['qnt'] }}</strong>    bales 

    </span>&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;

    <span>
    {{ $tmpProduct->name }} 

    </span> &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;

    <span>

    {{ $unitType->name }}

    </span>&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;


                                        </li>


</ul>

            @endforeach
        @endif
    </tbody>

    <tbody>
        @if (count($productionitemsx) === 0)
            <tr>
                <td colspan="4">----</td>
            </tr>
        @else
        <tr>
                <td colspan="4"><strong>Extruding</strong></td>
            </tr>
            @foreach ($productionitemsx as $productionitem)
                @php
                    $unitType = $unittypes[$productionitem->unitId]; 
                    $tmpProduct = $porducts[$productionitem->productId];
                @endphp

                <tr>
                    <td>{{ $tmpProduct->name }} </td>
                    <td>{{ $unitType->name }} </td>
                    <td>{{ $productionitem->weight }}</td>
                    <td><button type="button" class="btn btn-outline-info"  onclick="changestate(this)"   data-value="{{ $productionitem->id }}"      >Deduct</button></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

                @foreach ($workspaces as $workspace)


                @php
                
                $state = $workspace->state;
                $productionId = $workspace->productionId;

                @endphp

              
                  
           
                @endforeach
               
              
               
            
              </tr>
        
              <!-- Add more rows as needed -->
            </tbody>
          </table>
          
        </div>
      </div>
    </div>
  </div>
</div>




<!-- <div class="col-md-6">
      <div class="card">
        <div class="card-header text-center">
        <h3>
            <strong>Work Space </strong></h3>
        </div>
        <div class="card-body">
          
        </div>
      </div>
    </div>
  </div>
</div> -->
<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    
    function complete(){


      var answer = window.confirm("Do you want to complete production?");

            if (answer) {
            
            } else {

              return false;
            
            }

      // alert('Jax' );
      var productionId = document.getElementById("productionId").value;
    
      $.ajax({
        //alert('Jax' );
        url: "{{ route('complete') }}",
        type: 'post',
        data: {_token:CSRF_TOKEN,productionId :productionId},
        dataType: 'json',
        success: function(response){
      
          var deleteButtons = document.getElementById("myButton");
          button.disabled = true;

        
         

       
         
        }


        });

     

        


}
</script>
<br>
<!-- <button type="button" class="btn btn-lg btn-primary" id="complete" onclick="complete()">Complete</button> -->

</body>
</html>