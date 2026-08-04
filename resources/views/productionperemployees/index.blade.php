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


<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script src="{{ asset('public/js/script.js') }}" ></script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >View Production</th>



</tr>
</thead>
</table>
<br>
<br>
</div>
</div>
</div>
<a class="btn btn-outline-success" href="{{ route('productionperemployees.create') }}">  Create Production  </a>
<div>
    <br>
</div>
<table class="table table-striped" >
<tr>
<th  scope="col"> Id</th>
<th  scope="col"> Reference No</th>
<th  scope="col"> Date</th>
<th  scope="col"> Machine</th>
<th  scope="col"> Shift</th>
<th  scope="col"> Process</th>
<th  scope="col"> State</th>
<th  scope="col" width="300px"> Action</th>
</tr>

@foreach ($productionperemployee as $productionperemployee)
@php $tmpShifttype = $shifttypes[$productionperemployee->shiftId]; @endphp
@php $tmpProcesstype = $processtypes[$productionperemployee->processId]; @endphp
@php $tmpMachinetype = $machinetypes[$productionperemployee->machineryId]; @endphp
@php $tmpstatetype = $statustypes[$productionperemployee->stateId]; @endphp
<tr>
<td>{{ $productionperemployee->id }}</td>
<td>{{ $productionperemployee->refNo }}</td>
<td>{{ $productionperemployee->created_at }}</td>
<td>{{ $tmpMachinetype->name }}</td>
<td>{{ $tmpShifttype->name }}</td>
<td>{{ $tmpProcesstype->name }}</td>
<td>{{ $tmpstatetype->name }}</td>


@if ($tmpstatetype->name == 'Finished')
<td>
<form action="{{ route('productionperemployees.destroy',$productionperemployee->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('productionperemployees.show',$productionperemployee->id) }}" >View </a> 


<button type="submit" class="btn btn-outline-info"  disabled>Update</button>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info"  disabled>Delete</button>
</form>
</td>
@else
<td>
<form action="{{ route('productionperemployees.destroy',$productionperemployee->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('productionperemployees.show',$productionperemployee->id) }}" >View </a> 
<a class="btn btn-outline-info" href="{{ route('productionperemployees.edit',$productionperemployee->id) }}">Update</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
</form>
</td>
@endif


</td>
</tr>
@endforeach

</table>

</body>
</html>