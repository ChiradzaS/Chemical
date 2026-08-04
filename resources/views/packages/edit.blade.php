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

<title>Add Packaging </title>

<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<script>


function calculateValuePerUnitPack() {
  var e = document.getElementById("unitTypeId");
    var valueE = e.options[e.selectedIndex].value;// get selected option value
    //alert(" select value : " + valueE);
    var textE = e.options[e.selectedIndex].text;
    //alert(" select text E : " + textE);
   
    for (var key in valArray) {
     //alert("key " + key + " has value " + valArray[key]);
      
      var rtnComp = textE.localeCompare(key);
      //alert("RTN : "+rtnComp+"key " + key + " Compare : " + textE);
            if (rtnComp == 0) {
              
              var unitDivide = 1000 / valArray[key];
            
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


</script>
<body>
<div class=".container mt-2">
  <div class="row">
  <div class="col-lg-12 margin-tb">
  <div class="pull-left mb-2">
</div>
<h2>Update Packaging for Product</h2>
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


<form id="updatepackages" action="{{ route('packages.update',$package->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')


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
      
      
      <th  scope="col"> Product : <input type="text"  value="{{$package->productId}}"  name="productId"   readonly ></th>
      <th  scope="col"  > Product Name: <textarea name="product"  value="{{$porduct->name}}"  readonly  >{{$porduct->name}}</textarea></th>
      <th  scope="col"> Barcode : <input type="text"  name="barcode" value="{{ $porduct->barcode}}" readonly ><br></th>  
      <th  scope="col"> WeightPerProduct :
<input type="text"  name="WeightPerProduct"  id="WeightPerProduct" value="{{$porduct->WeightPerProduct}}" readonly  ></th>
  </tr>
    <tr>
      
      
        <th  scope="col"> Min Weight:(kg) : <input type="text" value="{{$porduct->minWeightPerProduct}}" name="minWeightPerProduct"  readonly ></th>
        <th  scope="col"> Avg Weight:(kg) : <input type="text" value="{{$porduct->avgWeightPerProduct}}"  name="avgWeightPerProduct"  readonly></th>
        <th  scope="col"> Max Weight:(kg) : <input type="text" value="{{$porduct->maxWeightPerProduct}}" name="maxWeightPerProduct"  readonly ></th>
        <th  scope="col"> <select disable name="unitTypeId"   >
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
    @foreach($unittypes as $unittype)
    <option value="{{ $unittype->id }}" >{{ $unittype->name }} </option>
    @endforeach
    </select><br>

<strong>Minimum weight(kg):</strong>
<input type="text" name="minWeight" value="{{ $package->minWeight}}" id="minWeight">

<strong>Average weight(kg):</strong>
<input type="text" name="avgWeight"   value="{{ $package->avgWeight}}"  id="avgWeight" >

<strong>Maximum weight(kg):</strong>
<input type="text" name="maxWeight"   value="{{ $package->maxWeight}}"  id= "maxWeight" >
<br>



<strong>Enter Outer Packaging Product:</strong>
<select  name="outerPackagePerProductId" id="outerPackagePerProductId" class="js-example-basic-single" >
@foreach ($porducts as $porduct)
<option value="{{ $porduct->id }}" @if($package->outerPackagePerProductId==$porduct->id) selected @endif >{{ $porduct->name }}</option>
@endforeach
</select>



<br>
<strong>Must print a label</strong>
<select  name="printLabel" >
            <option @if($package->printLabel=='0')  selected @endif value="0" >No</option>   
            <option @if($package->printLabel=='1')  selected @endif value="1" >Yes</option> 
</select>


<strong>Must print a barcode</strong>
<select  name="prnBarcode" >
            <option @if($package->prnBarcode=='0')  selected @endif value="0" >No</option>   
            <option @if($package->prnBarcode=='1')  selected @endif value="1" >Yes</option> 
    </select>

<strong>Must print a serial number</strong>
<select  name="prnSerialNo" >
            <option @if($package->prnSerialNo=='0')  selected @endif value="0" >No</option>   
            <option @if($package->prnSerialNo=='1')  selected @endif value="1" >Yes</option> 
    </select>

<br>
<strong>Enter barcode:</strong>
<input type="text" name="barcode" value="{{ $package->barcode}}" >
<strong>or set customer barcode:</strong>
<input type="text" name="custBarcode" value="{{ $package->custBarcode}}" >


<br>
<strong>Label line 1:</strong>
<input type="text" name="labelLine1"  value="{{ $package->labelLine1}}"><br>
<strong>Label line 2:</strong>
<input type="text" name="labelLine2"  value="{{ $package->labelLine2}}"><br>
<strong>Label line 3:</strong>
<input type="text" name="labelLine3"  value="{{ $package->labelLine3}}"><br>
<strong>Label line 4:</strong>
<input type="text" name="labelLine4"  value="{{ $package->labelLine4}}" ><br>
<strong>Enter other info:</strong>
<input type="text" name="otherInfo"  value="{{ $package->otherInfo}}">
<strong>Enter other packaging details:</strong>
<!-- <input type="text" name="otherPackagingDetails" ><br> -->
<textarea name="otherPackagingDetails" >{{ $package->otherPackagingDetails}}</textarea>
<strong>Ratio to product:</strong>
<input type="text" name="ratioToProduct" value="{{ $package->ratioToProduct}}">

<br>

<br>
<button type="submit" href="{{ route('packages.create',['productId'=>$package->productId]) }}" class="btn btn-outline-info">Save</button>
<button type="submit" onclick="history.back()" padding-right=5px class="btn btn-primary btn-sm" >Back</button> 
</div>

</form>
</body>

</div>
</html>