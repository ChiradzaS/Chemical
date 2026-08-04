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

<title>Software</title>

<meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<script>


function calculateValuePerUnitPack() {
  var e = document.getElementById("unitTypeId");
  var valueE = e.options[e.selectedIndex].value;// get selected option value
  var unitType = e.options[e.selectedIndex].text;
  var textE = e.options[e.selectedIndex].text;


  var bagtype = document.getElementById('bagType').value;  
  var comboBoxBagType = document.getElementById("bagType");
  var bagTypeValue = comboBoxBagType.value;
  var bagTypeText = comboBoxBagType.options[comboBoxBagType.selectedIndex].text;

  

 


   
    for (var key in valArray) {
     //alert("key " + key + " has value " + valArray[key]);
      
      var rtnComp = textE.localeCompare(key);
      //alert("RTN : "+rtnComp+"key " + key + " Compare : " + textE);
            if (rtnComp == 0) {
              
              var unitDivide = 1000 / valArray[key];
              if(bagTypeText.trim() == 'Rolls' || bagTypeText.trim() == 'Centre Fold') {
            }
            if (unitType.trim() == 'per m' || unitType.trim() == 'kg') 
            {}

         
            
              var weightPerProduct1000 = document.getElementById("WeightPerProduct").value;
              var unitWeight =  weightPerProduct1000 / unitDivide;
              
              var percAvgWeight = document.getElementById("percentAvgWeightPerProduct").value;
              var percMinWeight = document.getElementById("percentMinWeightPerProduct").value;
              var percMaxWeight = document.getElementById("percentMaxWeightPerProduct").value;
              
              var avgWeight = unitWeight + unitWeight * (percAvgWeight/100);
              var minWeight = unitWeight + unitWeight * (percMinWeight/100);
              var maxWeight = unitWeight + unitWeight * (percMaxWeight/100);
           


              document.getElementById("avgWeight").value = avgWeight;
              document.getElementById("minWeight").value = minWeight;
              document.getElementById("maxWeight").value = maxWeight;
            }
    }


    

       

  
}





var valArray = { 
@foreach($unittypes as $unittype)
 "{{ $unittype->name }}" : {{ $unittype->value }} , 
@endforeach
}

  function display() {


       
       
        var val = Date.now()
        const uniqueId = Math.random().toString(36).substr(2, 22);

        var nId = ""+uniqueId+val;
        var valT = nId.toString().substr(7,13);
       
        const myElement = document.getElementById("barcode");
        myElement.value = valT;


      
  }


  function checkinfo() {

  var weight = document.getElementById("avgWeight").value ;
       if(weight.length <=0){
       alert('Please select the unit type')
       return;

       }
      }

</script>
<body>
    <div>
{{-- @include('view') --}}

<div class=".container mt-2">
  <div class="row">
  <div class="col-lg-12 margin-tb">
  <div class="pull-left mb-2">
</div>
   
    
    
<h2>Create Packaging for Product</h2>
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
<form action="{{ route('packages.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />




<table class="table table-striped" width="100%">

<tr>
      
      
      <th  scope="col"> Product : <input type="text"  value="{{$productId}}"  name="productId"  readonly  ></th>
      <th  scope="col"  > Product Name: <textarea name="product"  readonly >{{$porduct->name}}</textarea></th>
      <th  scope="col"> Barcode : <input type="text" value="{{$porduct->barcode}}" name="barcode" readonly ><br></th>  
      <th  scope="col"> WeightPerProduct :
<input type="text"  name="WeightPerProduct"  id="WeightPerProduct" value="{{ $porduct->WeightPerProduct }}"></th>
<th Bag Type: scope="col">
  <select  id="bagType" name="bagType">
@foreach($bagtypes as $bagtype)
<option value="{{ $bagtype->id }}" @if($porduct->bagType== $bagtype->id ) selected @endif>{{ $bagtype->name }}</option>
@endforeach
</select>
</th>
  </tr>
    <tr>
      
      
        <th  scope="col"> Min Weight:(kg) : <input type="text" value="{{$porduct->minWeightPerProduct}}" name="minWeightPerProduct"  readonly ></th>
        <th  scope="col"> Avg Weight:(kg) : <input type="text" value="{{$porduct->avgWeightPerProduct}}"  name="avgWeightPerProduct"  readonly></th>
        <th  scope="col"> Max Weight:(kg) : <input type="text" value="{{$porduct->maxWeightPerProduct}}" name="maxWeightPerProduct"  readonly ></th>
        <th  scope="col"> <select  name="unitTypeId"   >
    @foreach($unittypes as $unittype)
    <option disabled value="{{ $unittype->id }}"  @if($unittype->id==$porduct->unitPackId) selected @endif >{{ $unittype->name }} </option>
    @endforeach
    </select></th>
    </tr>
   
   
      
      
      <th  scope="col"> MinWeight:(%) : <input type="text" value="{{$porduct->percentMinWeightPerProduct}}"  id="percentMinWeightPerProduct" name="percentMinWeightPerProduct"    readonly></th>
      <th  scope="col"> AvgWeight:(%) : <input type="text" value="{{$porduct->percentAvgWeightPerProduct}}"   id="percentAvgWeightPerProduct" name="percentAvgWeightPerProduct"  readonly></th>
      <th  scope="col"> MaxWeight:(%) : <input type="text" value="{{$porduct->percentMaxWeightPerProduct}}"  id="percentMaxWeightPerProduct"name="percentMaxWeightPerProduct"   readonly></th>
      <th> &nbsp</th>
  </tr>
 

   
    

    
    
</table>




<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}
</style>
<hr>

<strong>Select unit type:</strong>
<select  name="unitTypeId"  id="unitTypeId" onchange="calculateValuePerUnitPack()"  class="js-example-basic-single" >
<option   >&nbsp;&nbsp;&nbsp;&nbsp;--unit-- </option>
    @foreach($unittypes as $unittype)
    <option  value="{{ $unittype->id }}" >{{ $unittype->name }} </option>
    @endforeach
    </select><br>

<strong>Minimum weight(kg):</strong>
<input type="text" name="minWeight" id="minWeight">

<strong>Average weight(kg):</strong>
<input type="text" name="avgWeight"   id="avgWeight" >

<strong>Maximum weight(kg):</strong>
<input type="text" name="maxWeight"   id= "maxWeight" >
<br>



<strong>Enter Outer Packaging Product:</strong>
<select  name="outerPackagePerProductId" id="outerPackagePerProductId" onchange="checkinfo()" class="js-example-basic-single"  >
<option  disabled selected hidden>-- select product --</option>
@foreach ($porducts as $porduct)

<option value="{{ $porduct->id }}" >{{ $porduct->name }}</option>
@endforeach
</select>
@error('outerPackagePerProductId')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror



<br>
<strong>Must print a label</strong>
<select  name="printLabel" >
<option value="1" >Yes</option>
<option value="0" >No</option>
</select>


<strong>Must print a barcode</strong>
<select  name="prnBarcode" >
    <option value="1" >Yes</option>
    <option value="0" >No</option>
    </select>

<strong>Must print a serial number</strong>
<select  name="prnSerialNo" >
    <option value="1" >Yes</option>
    <option value="0" >No</option>
    </select>

<br>
<strong>Generate barcode:</strong>
<input type="text" name="barcode" id="barcode" >
<button type="button" onclick="display()"  padding right=5px  class="btn btn-dark"> Barcode </button>&nbsp&nbsp&nbsp&nbsp
@error('barcode')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
<strong>or set customer barcode:</strong>
<input type="text" name="custBarcode" >


<br>
<strong>Label line 1:</strong>
<input type="text" name="labelLine1" ><br>
<strong>Label line 2:</strong>
<input type="text" name="labelLine2" ><br>
<strong>Label line 3:</strong>
<input type="text" name="labelLine3" ><br>
<strong>Label line 4:</strong>
<input type="text" name="labelLine4" ><br>
<strong>Enter other info:</strong>
<input type="text" name="otherInfo" >
<strong>Enter other packaging details:</strong>
<!-- <input type="text" name="otherPackagingDetails" ><br> -->
<textarea name="otherPackagingDetails" ></textarea>
<strong>Ratio to product:</strong>
<input type="text" name="ratioToProduct" >

<br>

<button type="submit" padding-right=5px class="btn btn-primary btn-sm"  >Add</button> 
<a class="btn btn-outline-info" href="{{ route('packages.index') }}">Back</a>
</div>
</form>
<table class="table table-striped" width="100%">
    <tr>
        <th  scope="col"> Id</th>
        <th  scope="col"> Outer Packaging</th>
        <th  scope="col"> Min Weight</th>
        <th  scope="col"> Avg Weight</th>
        <th  scope="col"> Max Weight</th>
        <th  scope="col"> Unit</th>
  
    
    <th  scope="col" width="200px"> Action </th>
    </tr>
    @foreach ($packages as $package)
       @php $tmpProduct = $porducts[$package->outerPackagePerProductId]; @endphp

      @php $tmpUnittype = $unittypes[$package->unitTypeId]; @endphp
    <tr>
        <td>{{ $package->id }}</td>
        <td>{{ $tmpProduct->name }}</td>
        <td>{{ $package->minWeight }}</td>
        <td>{{ $package->avgWeight }}</td>
        <td>{{ $package->maxWeight }}</td>
        <td>{{ $tmpUnittype->name }}</td>
        
    <td>
        <form action="{{ route('packages.destroy',$package->id) }}" method="Post">
            <a class="btn btn-outline-info" href="{{ route('packages.edit',$package->id) }}">Update</a>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-info" >Delete</button> 
        </form>           
    </td>
    </tr>
    @endforeach
</body>
</div>
</html>