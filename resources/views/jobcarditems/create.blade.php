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


    
    function display() {
       
        var val = Date.now()
        const uniqueId = Math.random().toString(36).substr(2, 22);

        var nId = ""+uniqueId+val;
        var valT = nId.toString().substr(7,13);
       
        //window.alert(" Barcode : "+valT);

        const myElement = document.getElementById("barcode");
        myElement.value = valT;

           }
   
  </script>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Add JobCard Item</h2>
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

<strong>Job Card id:</strong>
<input type="text" name="jobCardId" value="{{$jobCardId}}" readonly ><br>

<br>
<strong>Product:</strong>
<select  name="productId" >
@foreach ($porducts as $porduct)
<option value="{{ $porduct->id }}" >{{ $porduct->name }}</option><br>
@endforeach
</select>

<br>


<strong>Unit type:</strong>
<select  id="unitId" name="unitId">
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}"  >{{ $unittype->name }}</option>
<option value="" disabled selected hidden>-- select unitType name --</option>
@endforeach
</select>

<br>

<strong>Bag Type:</strong>
<select  id="bagType" name="bagType">
@foreach($bagtypes as $bagtype)
<option  disabled selected hidden>-- select bag Type --</option>
<option value="{{ $bagtype->id }}" >{{ $bagtype->name }}</option>
@endforeach
</select>
<

<strong>Process type:</strong>
<select  name="processId"      >
@foreach($processtypes as $processtype)
<option value="{{ $processtype->id }}" >{{ $processtype->name }} </option>
@endforeach
</select>

<br>
<strong>Job Card Item name:</strong>
<input type="text" name="name" >

<br>



<strong>Quantity:</strong>
<input type="text" name="qnt" >

<br>


<strong>Barcode:</strong>
<input type="text" id="barcode" name="barcode" >
<button type="button" onclick="display()"  padding right=5px  class="btn btn-dark"> Barcode </button>&nbsp&nbsp&nbsp&nbsp
<br>


<strong>Other:</strong>
<textarea name="other" id="other" ></textarea><br>


<br>




<button type="submit" class="btn btn-outline-info">Submit</button>
</div>
</form>
</div>
</body>
</html>