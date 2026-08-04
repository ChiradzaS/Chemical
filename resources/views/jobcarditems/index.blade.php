
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


</head>
<script type="text/javascript">
     
    function calculate() {

         var micron = document.getElementById("thickness").value;
       
         var width = document.getElementById("totalWidth").value;
        
         var process = document.getElementById("processId").value;
        
         var test = document.getElementById("test").value;
       

         

         if (process == 23){

            var testingweight =  micron * width / 5600;
           

            document.getElementById('test').value = testingweight;

         }
         else if(process != 23){

            alert("Testing weight is only for extruding process");

         }

         
        
    }
   

  </script>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
    <br>
<br>
<th >View Jobcard details</th>



</tr>
</thead>
</table>

<br>
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
<form action="{{ route('jobcarditems.store') }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<strong>Jobcarditem:</strong>
<input type="text" name="id" value="{{$jobcarditem->id}}" readonly > 

<strong>Product:</strong>
@php $tmpProduct = $porducts[$jobcarditem->productId]; @endphp
<!-- <input type="text" name="productId" value="{{$tmpProduct->name}}" readonly> -->
<textarea name="productId" >{{$tmpProduct->name}}</textarea>


<!-- <br><strong>Process type:</strong>
@php $tmpProcessType = $processtypes[$jobcarditem->processId ]; @endphp
<input type="text" name="processId"  id="processId" value="{{$tmpProcessType->name }}" >
@php Cache::pull('processName',$tmpProcessType->name); @endphp -->

<strong>Process type:</strong>
<select  name="processId"   id="processId"  >
@foreach($processtypes as $processtype)
<option value="{{ $processtype->id }}"  @if($jobcarditem->processId==$processtype->id) selected @endif>{{ $processtype->name }}</option>
@endforeach
</select>

<strong>Job Card id:</strong>
<input type="text" name="jobCardId"  value="{{$jobcarditem->jobCardId }}"readonly>

<strong>Material Type</strong>
@php $tmpMaterialType = $materialtypes[$porduct->materialTypeId]; @endphp
<input type="text" name="materialType" value="{{$tmpMaterialType->name}}" >
<br>

<strong>Bag Type:</strong>
<select  id="bagType" name="bagType">
@foreach($bagtypes as $bagtype)
<option value="{{ $bagtype->id }}" @if($jobcarditem->bagType==$bagtype->id) selected @endif>{{ $bagtype->name }}</option>
@endforeach
</select>


<strong>Length(mm):</strong>
<input type="text" value="{{$porduct->product_length}}" >



<strong>Thickness(mic):</strong>
<input type="text"  id="thickness" value="{{$porduct->thickness}}" >
<br>
<strong>Width(mm):</strong>
<input type="text" id="totalWidth"  value="{{$porduct->product_Width}}" >

<strong>Gusset Width(mm):</strong>
<input type="text" id="totalWidth"  value="{{$porduct->	gussetWidth}}" >

<strong>Total Width(mm):</strong>
<input type="text" id="totalWidth"  value="{{$porduct->totalWidth}}" >



<br><strong>Job Card Item name:</strong>
<input type="text" name="name"   value="{{$jobcarditem->name }}"  readonly>


<strong>Quantity:</strong>
<input type="text" name="qnt"  value="{{$jobcarditem->qnt }}" readonly>

<strong>Colour:</strong>
@php $tmpProductType = $colourtypes[$porduct->color]; @endphp
<input type="text" name="color" value="{{$tmpProductType->name}}" >


<br>

<strong>Unit:</strong>
@php $tmpUnitType = $unittypes[$jobcarditem->unitId ]; @endphp
<input type="text" name="unitId" value="{{ $tmpUnitType->name }}" readonly >

<br><strong>Barcode:</strong>
<input type="text" name="barcode" value="{{$jobcarditem->barcode }}" readonly>


<strong>Other:</strong>
<textarea name="other" id="other" value="{{$jobcarditem->other}}" readonly>{{$jobcarditem->other}}</textarea><br>

<strong>Testing Weight(g):</strong>
<input type="text" name="test" id="test" readonly>
<a onclick="calculate()" class="blue">Display Testing weight</a>&nbsp&nbsp&nbsp&nbsp
<br>
<br>
<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}


 
    h3{
        text-align: center;
    }
 </style>
 <hr>


 @php $tmpProcesstype = $processtypes[$jobcarditem->processId]; @endphp
 
 @if($tmpProcesstype->name == 'Extruding' )



 <h3>Roll Weight</h3>

<table   class="table table-striped" width="100%">
    <tr id="list1" >
        <th  scope="col">Roll weight </th>
        <th  scope="col"> {{$jobcarditem->qnt}} Kg</th>

        
    </tr>

    </table>
<br>


@else

<h3>Packaging List</h3>


<table   class="table table-striped" width="100%">
    <tr id="list1" >
        <th  scope="col"> Id</th>
        <th  scope="col"> Min Weight</th>
        <th  scope="col"> Avg Weight</th>
        <th  scope="col"> Max Weight</th>
        <th  scope="col"> Unit</th>
        
        
    </tr>
    @foreach ($productList as $product)
    @php $tmpUnittype = $unittypes[$product->unitTypeId]; @endphp
    @php $tmpUnitPack = $unittypes[$product->unitPackId]; @endphp
   
    <tr>
        <td>{{ $product->id }}</td>
        <td>{{ $product->minWeight }}</td>
        <td>{{ $product->avgWorkingWeight }}</td>
        <td>{{ $product->maxWeight }}</td>
        <td><strong>{{ $tmpUnittype->name }}</strong></td>
        
    </tr>
    <tr>
        <td>{{ $product->id }}</td>
        <td>{{ $product->minWeightPerProduct }}</td>
        <td>{{ $product->avgWeightPerProduct }}</td>
        <td>{{ $product->maxWeightPerProduct }}</td>
        <td><strong>{{ $tmpUnitPack->name }}</strong></td>
    </tr>
        @endforeach
    </table>


    @endif


    

<button type="button" onclick="history.back()" class="btn btn-outline-info">Back</button>
<a class="btn btn-outline-info" href="{{ route('index.create', ['jobCardId' => $jobcarditem->jobCardId, 'other' => $jobcarditem->other, 'state' => $jobcarditem->stateId,'jobcarditemId' => $jobcarditem->id ,'productId' => $jobcarditem->productId,'process' => $tmpProcessType->name])  }}">Print</a>
</div>
</form>

</div>
</body>
</html>