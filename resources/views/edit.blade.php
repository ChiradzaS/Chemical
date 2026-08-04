<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<title>Add job_cards Form - Laravel 8 CRUD</title>

</head>
<body>
<div>
<br>
</div>
<div class="container mt-2">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h3>Update Job Cards</h3>
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
<form action="{{ route('job_cards.update',$job_card->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<strong>Product:</strong>

<select  id="productId" name="productId"   >
@foreach($porducts as $porduct)
<option value="{{ $porduct->id }}"   @if($job_card->productId==$porduct->id) selected @endif>{{ $porduct->name }}</option>
@endforeach
</select>
</div>


{{-- <strong>Material Type</strong>
    @php $tmpMaterialType = $porducts[$job_card->productId]; @endphp
    <input type="text" name="materialType" value="{{$tmpMaterialType->materialTypeId}}" >


    <strong>Colour:</strong>
@php $tmpProductType = $porducts[$job_card->productId]; @endphp
<input type="text" name="color" value="{{$tmpProductType->color}}" > --}}
   



Material Type:
<select name="" id=""  >
@foreach( $materialtypes as $materialtype)
<option value="{{  $materialtype->id }}"  @if($product->materialTypeId==$materialtype->id) selected @endif>{{ $materialtype->name }}</option>
@endforeach
</select>
</div>

Colour:
<select name="color"  id="porducts"   placeholder="-- Select Product --">
@foreach($colourtypes as $colourtype)
<option value="{{ $colourtype->id }}" @if($product->color==$colourtype->id) selected @endif>{{ $colourtype->name }}</option>
@endforeach
</select>
</div>
  



<br> 


<strong>Unit</strong>
<select name="unit" id="unit" >
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}" @if($job_card->unit==$unittype->id) selected @endif>{{ $unittype->name }}</option>
@endforeach
</select>
</div>
</div>

<strong>Quantity:</strong>
<input type="text" name="qntUnit" value="{{ $job_card->qntUnit }}" ><br>

<strong>Name:</strong>
<input type="text" name="name"  value="{{ $job_card->name }}"  ><br>





<strong>Ref No:</strong>
<input type="text" name="refNo"  value="{{ $job_card->refNo }}" ><br>




<strong>description:</strong>
<input type="text" name="description"  value="{{ $job_card->description }}" ><br>



<strong>noOfProcesses:</strong>
<input type="text" name="noOfProcesses"   value="{{ $job_card->noOfProcesses }}"><br>



<strong>state:</strong>
<input type="text" name="state"  value="{{ $job_card->state }}"><br>

<strong>Enter Start Date:</strong>
<input type="date" name="startDate"  value="{{ $job_card->startDate }}"  ><br>
<br>
@foreach($jobcarditems as $jobcarditem)
@php $tmpProcesstype = $processtypes[$jobcarditem->processId]; @endphp
@endforeach
<button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Save</button>
<a class="btn btn-outline-info" href="{{ route('jobcarditems.create', ['jobCardId' => $job_card->id]) }}">Add</a>
<a class="btn btn-outline-info" href="{{ route('index.index', ['jobCardId' => $job_card->id ,'productId' => $job_card->productId, 'prntReport' =>'JOBCARD_ITEMS_BY_JOBCARDID'])  }}">Print</a>
</div>
</form>
 <style>
    h3{
        text-align: center;
    }
 </style>
<h3>Job Card items</h3>

<table class="table table-striped" >
    <tr>
    <th  scope="col"> Id</th>
     <th  scope="col"> Product </th> 
    <th  scope="col"> Name </th>
    <th  scope="col"> Process</th>
    <th  scope="col"> Qnt</th>
    <th  scope="col"> Unit Id</th>
    <th  scope="col"> Barcode</th>
    <th  scope="col" width="300px"> Action</th>
    
    </tr>


@foreach($jobcarditems as $jobcarditem)
@php $tmpProduct = $porducts[$jobcarditem->productId]; @endphp
@php $tmpUnittype = $unittypes[$jobcarditem->qntId]; @endphp
@php $tmpProcesstype = $processtypes[$jobcarditem->processId]; @endphp
<tr>
<td><strong>{{$jobcarditem->id}}</strong></td>
<td><strong>{{$tmpProduct ->name}}</strong></td>
<td><strong>{{$jobcarditem->name}}</strong></td>
<td><strong>{{$tmpProcesstype->name}}</strong></td>
<td><strong>{{$jobcarditem->qnt}}</strong></td>
<td><strong>{{$tmpUnittype->name}}</strong></td>
<td><strong>{{$jobcarditem->barcode}}</strong></td>
<td>
<form action="{{ route('jobcarditems.destroy',$jobcarditem->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('jobcarditems.index',['productId' => $jobcarditem->productId, 'id' => $jobcarditem->id]) }}">View</a>   
<a class="btn btn-outline-info" href="{{ route('jobcarditems.edit',[$jobcarditem->id,'productId' => $jobcarditem->productId, 'id' => $jobcarditem->id]) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit"  class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>

</form>
</td>
</tr>

@endforeach

</body>
</html>