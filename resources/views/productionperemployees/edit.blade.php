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
<h2>Edit Production </h2>
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

<form  action="{{ route('productionperemployees.update',$productionperemployee->id) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="form-row">
    <div class="form-group col-md-6">
      <strong for="inputEmail4"></strong>
      <input type="text" class="form-control" name="productionId"  value="{{ $productionperemployee->id }}" hidden>
    </div>
  </div>

  <div class="form-row align-items-center">
          <div class="form-group col-md-6">
            <strong class="mr-sm-2" for="inlineFormCustomSelect">Process</strong>
            <select class="custom-select mr-sm-2"  id="processId" name="processId">
              @foreach($processtypes as $processtype)
               <option value="{{ $processtype->id }}"  @if($processtype->id==$productionperemployee->processId) selected @endif   >{{ $processtype->name }} </option>
                 @endforeach
                 </select>
          </div>
        
          <div class="form-group col-md-6">
            <strong class="mr-sm-2" for="inlineFormCustomSelect">Machine</strong>
            <select class="custom-select mr-sm-2"  id="machineryId" name="machineryId">
               @foreach($machinetypes as $machinetype)
                <option value="{{ $machinetype->id }}" @if($machinetype->id==$productionperemployee->machineryId) selected @endif    >{{ $machinetype->name }} </option>
                 @endforeach
                </select>
          </div>
        </div>

        <div class="form-row align-items-center">
          <div class="form-group col-md-6">
            <strong class="mr-sm-2" for="inlineFormCustomSelect">Shift</strong>
            <select class="custom-select mr-sm-2" id="shiftId" name="shiftId">
             @foreach($shifttypes as $shifttype)
               <option value="{{ $shifttype->id }}" @if($shifttype->id==$productionperemployee->shiftId) selected @endif   >{{ $shifttype->name }} </option>
                @endforeach
                 </select>
          </div>
        
          <div class="form-group col-md-6">
            <strong class="mr-sm-2" for="inlineFormCustomSelect">Employee</strong>
            <select class="custom-select mr-sm-2"   id="employeeId" name="employeeId">
              @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @if($employee->id==$productionperemployee->employeeId) selected @endif>{{ $employee->name }} </option>
                 @endforeach              
                </select>
          </div>
        </div>


<input type="text" name="refNo"   value="{{ $productionperemployee->refNo }}"   hidden>


<textarea name="other" rows="2" cols="25" hidden>
{{ $productionperemployee->other }}
</textarea>



<input type="text" name="serialNo"  value="{{ $productionperemployee->serialNo }}" hidden>



<input type="text" name="user"  value="{{ $productionperemployee->user }}" hidden>



<input type="text" name="value"  value="{{ $productionperemployee->value }}" hidden>



<input type="text" name="stateId"  value="{{ $productionperemployee->stateId }}" hidden >
<br>
<button type="submit" class="btn btn-outline-info">Save</button>
<a class="btn btn-outline-info" href="{{ route('employeeitems.create',['productionId' => $productionperemployee->id]) }}">Add</a>
<a class="btn btn-outline-info" href="{{ route('productionperemployees.index') }}">Back</a>
<button type="submit"  name="myButton" value="finish" id=""  class="btn btn-outline-info">Finished</button>
</form>
<br>
<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
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
    <th  scope="col"> State</th>
    <th  scope="col"> Outstanding</th>
   
    <th  scope="col" width="300px"> Action</th>
    </tr>
    @foreach ($employeeitems as $employeeitem)
    @php $tmpUnittype = $unittypes[$employeeitem->unitId]; @endphp
    @php $tmpstatus = $statustypes[$employeeitem->stateId]; @endphp
    @php $tmpProduct = $porducts[$employeeitem->productId]; @endphp
    <tr>
    <td>{{ $employeeitem->id }}</td>
    <td>{{ $employeeitem->jobcarditemId }}</td>
    <td>{{ $tmpProduct->name }}</td>
    <td>{{ $employeeitem->qnt }}</td>
    <td>{{$tmpUnittype->name }}</td>
    @if ($employeeitem->outstanding == 0)

    <td>{{ "Completed" }}</td>
    @else
    <td>{{ $tmpstatus->name}}</td>
    @endif

    <td>{{ $employeeitem->outstanding }}</td>
   
    <td>
        
    <form action="{{ route('employeeitems.destroy',$employeeitem->id) }}" method="Post" >
    <a class="btn btn-outline-info" href="{{ route('employeeitems.show',$employeeitem->id) }}" >&nbspView&nbsp </a> 
    <a class="btn btn-outline-info" href="{{ route('employeeitems.edit',$employeeitem->id) }}" >Update</a>
   
    @csrf
    @method('DELETE') 
    <button type="submit" class="btn btn-outline-info" id="delete" value="delete" onclick="return confirm('Are you sure you want to delete')"  >Delete</button>
    </form>

    {{-- <a class="btn btn-default" href="#" role="button" 
    @if($disabled) disabled='disabled' @endif >Link</a> --}}
   
    @endforeach
</td>
</body>
</html>