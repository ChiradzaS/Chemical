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

<strong>Bag Type:</strong>
<select  id="bagType" name="bagType">
@foreach($bagtypes as $bagtype)
<option value="{{ $bagtype->id }}" @if($job_card->bagType==$bagtype->id) selected @endif>{{ $bagtype->name }}</option>
@endforeach
</select>
</div> 

<strong>Unit</strong>
<select name="unitId" id="unitId" >
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}" @if($job_card->unitId==$unittype->id) selected @endif>{{ $unittype->name }}</option>
@endforeach
</select>
</div>
</div>

&nbsp&nbsp<strong>Quantity :</strong>
<input type="text" id="qnt" name="qnt" value="{{$job_card->qnt}}" ><br>

<strong>Ref No:</strong>
<input type="text" name="refNo"  value="{{ $job_card->refNo }}" > &nbsp&nbsp

<strong>description:</strong>
<input type="text" name="description"  value="{{ $job_card->description }}" ><br>

    <strong>Length(mm):</strong>
    <input type="text" value="{{$porduct->product_length}}" >
    
    <strong>Thickness(mic):</strong>
    <input type="text"  value="{{$porduct->thickness}}" >
    
    <strong>Width(mm):</strong>
    <input type="text"  value="{{$porduct->product_Width}}" >
       
       
  
<strong>Other:</strong>
<textarea name="other" id="other" >{{$job_card->other}}</textarea><br>

<strong>noOfProcesses:</strong>
<input type="text" name="noOfProcesses"   value="{{ $job_card->noOfProcesses }}"><br>

<strong>state:</strong>
<input type="text" name="state"  value="{{ $job_card->stateId }}"><br>

<strong>Enter Start Date:</strong>
<input type="date" name="startDate"  value="{{ $job_card->startDate }}"  ><br>
<br>



<!-- <button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Save</button> -->
</form>
@foreach($jobcarditems as $jobcarditem)
@php $tmpProcesstype = $processtypes[$jobcarditem->processId]; @endphp
@endforeach

<a class="btn btn-outline-info" href="{{ route('jobcarditems.create', ['jobCardId' => $job_card->id]) }}">Add</a>
<a class="btn btn-outline-info" href="{{ route('index.index', ['jobCardId' => $job_card->id, 'productId' =>$job_card->productId,'prntReport' => 'JOBCARD_WITH_ITEMS']) }}">Print</a>
<a class="btn btn-outline-info" href="{{ route('jobcarditems.show',$job_card->id) }}" >Audit </a>
</div>
</form>
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
    <th  scope="col"> Outstanding</th>
    <th  scope="col"> Status</th>
    <th  scope="col" width="300px"> Action</th>
    
    </tr>


@foreach($jobcarditems as $jobcarditem)
@php $tmpProduct = $porducts[$jobcarditem->productId]; @endphp
@php $tmpUnittype = $unittypes[$jobcarditem->unitId]; @endphp
@php $tmpProcesstype = $processtypes[$jobcarditem->processId]; @endphp
@php $tmpstatus = $statustypes[$jobcarditem->stateId]; @endphp
<tr>
<td><strong>{{$jobcarditem->id}}</strong></td>
<td><strong>{{$tmpProduct ->name}}</strong></td>
<td><strong>{{$jobcarditem->name}}</strong></td>
<td><strong>{{$tmpProcesstype->name}}</strong></td>
<td><strong>{{$jobcarditem->qnt}}</strong></td>
<td><strong>{{$tmpUnittype->name}}</strong></td>
<td><strong>{{$jobcarditem->barcode}}</strong></td>
<td><strong>{{$$tmpstatus->name}}</strong></td>
<td><strong>{{$jobcarditem->outstanding}}</strong></td>
<td>
<form action="{{ route('jobcarditems.destroy',$jobcarditem->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('jobcarditems.index',['productId' => $jobcarditem->productId, 'id' => $jobcarditem->id]) }}">View</a>   
<a class="btn btn-outline-info" href="{{ route('jobcarditems.edit',[$jobcarditem->id,'productId' => $jobcarditem->productId, 'id' => $jobcarditem->id]) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit"  class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>

</td>
</tr>
<a class="btn btn-outline-info" href="{{ route('index.create', ['jobCardId' => $jobcarditem->jobCardId, 'other' => $jobcarditem->other, 'state' => $jobcarditem->stateId,'jobcarditemId' => $jobcarditem->id ,'productId' => $jobcarditem->productId,'process' => $tmpProcessType->name])  }}">Print</a>
@endforeach

</body>
</html>