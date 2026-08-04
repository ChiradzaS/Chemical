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

</head>
<body>
{{-- @include('view') --}}
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Production List</th>
</tr>
</thead>
</table>

</tr>
</thead>
</table>
</div>
<br>
<div class="pull-right">
</div>
</div>
</div>

<br>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('productions.update',$production['id']) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<table class="table table-striped" width="100%">

<tr>
      
      
      <th  scope="col"> Product : <input type="text" name="productionId"   value="{{ $production['id'] }}"   placeholder="Reference" readonly></th>
      <th  scope="col"  > Process:     <select  id="processId" name="processId"   disabled >
@foreach($processtypes as $processtype)
<option value="{{ $processtype->id }}"  @if($processtype->id==$production['processId']) selected @endif   >{{ $processtype->name }} </option>
@endforeach
</select></th>
      <th  scope="col"> Machine : <select  id="machineryId" name="machineryId"   disabled >
@foreach($machinetypes as $machinetype)
<option value="{{ $machinetype->id }}" @if($machinetype->id==$production['machineryId']) selected @endif    >{{ $machinetype->name }} </option>
@endforeach
</select> <br></th>  
      <th  scope="col"> Shift :    <select  id="shiftId" name="shiftId"   disabled>
@foreach($shifttypes as $shifttype)
<option value="{{ $shifttype->id }}" @if($shifttype->id==$production['shiftId']) selected @endif   >{{ $shifttype->name }} </option>
@endforeach
</select></th>
  </tr>
    <tr>
      
      
        <th  scope="col"> Employee :<select  id="userId" name="userId" class="js-example-basic-single">
@foreach($user as $user)
<option disabled  value="{{ $user->id }}" @if($user->id==$production['userId']) selected @endif>{{ $user->name }} </option>
@endforeach
</select></th>
        <th  scope="col"> Referrence : <input type="text" name="refNo"   value="{{ $production['refNo'] }}"   readonly></th>
        <th  scope="col"> Other : <textarea name="other" rows="2" cols="25" readonly>
{{ $production['other'] }}
</textarea></th>
        <th  scope="col"> serialNo: <input type="text" name="serialNo"  value="{{ $production['serialNo'] }}" readonly ></th>
    </tr>
    </table>
   
      
      

   
    

    
    





<input type="text" name="state"  value="{{ $production['stateId'] }}"  hidden>
<br>


</form>

<div>
<button class="btn btn-outline-info"  onclick="javascript:window.history.back();">Go Back</button>
<a class="btn btn-outline-info" href="{{ route('index.index',['productionId' => $production['id'],'state' => $production['stateId'],'other' => $production['other'], 'prntReport' => 'PRODUCTION_BY_PRODUCTIONID']) }}">Print</a>
</div>
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
<h3>Production items</h3>
<table class="table table-striped" >
    <tr>
    <th  scope="col"> Id No</th>
    <th  scope="col"> JobcarditemId</th>
    <th  scope="col"> Product</th>
    <th  scope="col"> Quantity</th>
    <th  scope="col"> Unit</th>
    <th  scope="col"> Status</th>
    <th  scope="col"> Outstanding</th>

    @foreach ($productionitems as $productionitem)
    @php $tmpUnittype = $unittypes[$productionitem['unitId']]; @endphp
    @php $tmpstatus = $statustypes[$productionitem['stateId']]; @endphp
    @php $tmpProduct = $porducts[$productionitem['productId']]; @endphp
    <tr>
    <td>{{ $productionitem['id'] }}</td>
    <td>{{ $productionitem['jobcarditemId'] }}</td>
    <td>{{ $tmpProduct->name }}</td>
    <td>{{ $productionitem['qnt'] }}</td>
    <td>{{ $tmpUnittype->name }}</td>
    <td>{{ $tmpstatus->name }}</td>
    <td>{{ $productionitem['outstanding'] }}</td>
   
    <td>
        
    <form action="{{ route('productionitems.destroy',$productionitem['id']) }}" method="Post" >
    
    @csrf
    @method('DELETE') 

    </form>

    {{-- <a class="btn btn-default" href="#" role="button" 
    @if($disabled) disabled='disabled' @endif >Link</a> --}}
   
    @endforeach
</td>
</body>
</html>