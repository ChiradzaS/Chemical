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






    <style>
        /* This styles the input tab */
        #myInput {
            background-color: red; /* Change this to the color you want */
        }
    </style>

<script>
    $(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<body>


<div>
<br>
</div>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">

</div>

</div>
</div>


<script>

    setTimeout(function () {
        var notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }, 5000); 
</script>


<div class=".container-fluid">
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Picking List</th>



</tr>
</thead>
</table>
</div>
<br>
<br>

<div class=".container-fluid">
<table class="table table-bordered">
<thead>

<style>
    .centred {
  text-align: center;
}
</style>

<tr>
    <th class="centred">Customer</th>
    <th class="centred">Product</th>
    <th class="centred">Quantity</th>
</tr>

</thead>
    


  <thead>
  @if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
  <form action="{{ route('pickingslip.store') }}" method="POST" enctype="multipart/form-data">
@csrf
    <tr>
      <th scope="col">
      <script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    function getorderitem() {


      var customer = document.getElementById("customerId").value;

      $.ajax({
                url: "{{ route('getorderitem') }}",
                type: 'post',
                data: {_token: CSRF_TOKEN, customer:customer},
                dataType: 'json',
                success: function(response){

                  setProduct(response);

                if (response['data'] === 1) {
    
                          alert('null');
                      }

               

               
                }

                });

                function setProduct(response){
                if(response['data'] != null){
                len = response['data'].length;
                }
                if (len > 0) {

                var html = '';
                for(var i=0; i<len; i++){
                  html += '<option value="' + response['data'][i].id + '">' + response['data'][i].name + '</option>';
     
     
     
               document.getElementById('productId').innerHTML = html;
               setpack()
        
     
}
}

}

                



    }

    

    </script>

  <select name="customerId" id="customerId"   class="js-example-basic-single" onchange="getorderitem()">
  <option value="" >---- select customer ----</option>
    @foreach($customers as $customer)
    <option value="{{$customer->id}}" >{{ $customer->name }} </option>
    @endforeach
    </select></th>
<th>



<script type='text/javascript'>
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    function setpack() {

        
        var dynamicCell = document.getElementById("dynamicCell");
        var productid = document.getElementById("productId").value;
        

        $.ajax({
                url: "{{ route('getProductbyid') }}",
                type: 'post',
                data: {_token: CSRF_TOKEN, productid: productid},
                dataType: 'json',
                success: function(response){
                setProduct(response);
                }
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
                       dynamicCell.style.display = 'block';
                       document.getElementById("unitId").value = "" + unitTypeId;
                       changeColor();
                    }
                  }
                } else {
                  if (len > 0) {
                    for(var i=0; i<len; i++){
                       var unitTypeId = response['data'][i].unitTypeId;
                       dynamicCell.style.display = 'block';
                       document.getElementById("unitId").value = "" + unitTypeId;
                       changeColor();
                    }
                  }
                }
              }
            }

    

    function changeColor() {
        
        var dynamicCell = document.getElementById("dynamicCell");

       
        if (dynamicCell) {
         
            dynamicCell.style.backgroundColor = '#A9A9A9';
        }
    }
</script>

<style>
        
        #dynamicCell {
            display: none;
        }
    </style>

  <select name="productId" id="productId"  class="js-example-basic-single" onchange="setpack()">
  <option value="0" disabled selected hidden>&ensp;&ensp; &ensp;&ensp;----------- Select Product ------------</option>
    @foreach($products as  $porduct)
<option value="{{ $porduct->id }}" ><strong>{{ $porduct->name }}</strong></option>
@endforeach
    </select></th>


    
      <script>
    // Function to handle the change event
    function handleDropdownChange() {
        var selectedValue = document.getElementById('productId').value;
       // console.log('Selected value:', selectedValue);

        // You can call your setpack() function or add more logic here based on the selected value
        ;
    }

    // Add an event listener to the change event of the dropdown
    document.getElementById('productId').addEventListener('change', handleDropdownChange);

    </script>

 
      <th scope="col" width="20%">
          <div class="input-group mb-3">
  <div class="input-group-prepend">
  <span class="input-group-text" id="basic-addon1"> <strong>Quantity Sent</strong></span>
  </div>
  <input type="text" name="qnt"  id="quantity" class="form-control" >
</div></th>
      <th scope="col" >
 
  <button type="submit" class="btn btn-primary btn-lg" onclick="check()">Picked</button>
</th>
    </tr>
  </thead>
  <tbody>

    <tr>
      <th scope="row"></th>

      <td colspan="3" id="dynamicCell">

        <select  name="unitId"  id="unitId" class="form-control" onchange="calculateValuePerUnitPack()"  >
        <option value="" >-- select unitPack name --</option>
        @foreach($unittypes as $unittype)
        <option value="{{ $unittype->id }}" >{{ $unittype->name }}</option>

@endforeach
</select></td> 
<script>

  function check() {

  

    var customer = document.getElementById("customerId").value;
    var product  = document.getElementById("productId").value;
    var unit     = document.getElementById("unitId").value;
    var quantity = document.getElementById("quantity").value;

    //alert('nn');

        if (!customer) {
            alert('Please select a customer');
            event.preventDefault();  
            return false;

        }

        if (!product) {
            alert('Please select a product');
            event.preventDefault(); 
            return false;
        }
        if (!unit) {
            alert('Please select a unit');
            event.preventDefault();  
            return false;
            
        }
        if (!quantity) {
            alert('Please enter  a quantity');
            event.preventDefault();  
            return false;

        }

            if (isNaN(quantity)) {
            alert('Quantity must be a number');
            event.preventDefault();  
            return false;
          }



 
}
</script>
     
    </tr>
  </tbody>
</table>


</form>
</div>
<br>
<div class=".container-fluid">
<div class="col-md-6 centred">
      <div class="card centtred">
        <div class="card-header text-center">
        <h3>
            <strong>Picked Items</strong></h3>
        </div>
        <div class="card-body">
          <table class="table table-striped centred">
            <tbody>
              <tr>
              <th  scope="col"> Customer</th>
              <th  scope="col"> Product</th>
              <th  scope="col"> unit</th>
              <th  scope="col"> Quantity</th>
              <th  scope="col"> Action</th>
            
            
              </tr>

              @php

              use Carbon\Carbon;


              $currentDate = Carbon::now()->toDateString();

              $pickings = \App\Models\Pickingslip::whereDate('created_at', $currentDate)
                                                ->where('stateId','<>' , 45)
                                                ->orderBy('updated_at', 'desc')
                                                ->get();
                                          
              @endphp


              @foreach ( $pickings as  $picking)

              @php $tmpProduct = $porducts[$picking->productId]; @endphp
              @php $tmpCust = $customers[$picking->customerId]; @endphp
              @php $tmpUnittype = $unittypes[$picking->unitId]; @endphp
       

   
              <tr>

           
       

              
              <td>{{$tmpCust->name}}</td>
              <td>{{$tmpProduct->name}}</td>
              <td>{{$tmpUnittype->name}}</td>
              <td>{{$picking->qnt}}</td>
              @if($picking->stateId == 61)
            <td>
                <form action="{{ route('pickingslip.destroy', $picking->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete')">&nbsp;&nbsp;Delete&nbsp;&nbsp;</button>
                </form>
            </td>
        @else
            <td>
                <button type="button" class="btn btn-outline-info">Delivered</button>
            </td>
        @endif

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
</div>


<br>

</body>
</html>