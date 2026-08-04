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
<h3>View Production</h3>

<br>
</div>
</div>
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

@foreach ($productions as $production)
@php $tmpShifttype = $shifttypes[$production->shiftId]; @endphp
@php $tmpProcesstype = $processtypes[$production->processId]; @endphp
@php $tmpMachinetype = $machinetypes[$production->machineryId]; @endphp
@php $tmpstatetype = $statustypes[$production->stateId]; @endphp
<tr>
<td>{{ $production->id }}</td>
<td>{{ $production->refNo }}</td>
<td>{{ $production->created_at }}</td>
<td>{{ $tmpMachinetype->name }}</td>
<td>{{ $tmpShifttype->name }}</td>
<td>{{ $tmpProcesstype->name }}</td>
<td>{{ $tmpstatetype->name }}</td>


@if ($tmpstatetype->name == 'Finished')
<td>
<form action="{{ route('productions.destroy',$production->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('productions.show',$production->id) }}" >View </a> 


<button type="submit" class="btn btn-outline-info"  disabled>Update</button>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info"  disabled>Delete</button>
</form>
</td>
@else
<td>
<form action="{{ route('productions.destroy',$production->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('productions.show',$production->id) }}" >View </a> 
<a class="btn btn-outline-info" href="{{ route('productions.edit',$production->id) }}">Update</a>
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