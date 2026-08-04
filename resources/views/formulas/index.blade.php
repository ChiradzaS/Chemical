<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<meta charset="UTF-8">

<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<!DOCTYPE html>

<h3>Formula List</h3>

</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<div>
<a class="btn btn-outline-info" href="{{ route('formulas.create') }}" enctype="multipart/form-data"> Create </a>
</div>
<br>
<table class="table table-striped" >
<tr>
<th  scope="col"> Id</th>
<th  scope="col"> Formula Name</th>
<th  scope="col"> Fomula Type</th>
<th  scope="col"> State</th>
<th  scope="col"> Date created</th>
<th  scope="col" width="200px"> Action</th>
</tr>
@foreach ($formulas as $formula)
@php $tmptype = $fomulartypes[$formula->type]; @endphp
<tr>
<td  style="color: {{ $formula->active > 0 ? 'red' : 'black' }}">{{ $formula->id }}</td>
<td style="color: {{ $formula->active > 0 ? 'red' : 'black' }}">{{ $formula->name }}</td>
<td  style="color: {{ $formula->active > 0 ? 'red' : 'black' }}">{{ $tmptype->name }}</td>
@if ( $formula->active > 0)
<td  style="color: {{ $formula->active > 0 ? 'green' : 'black' }}"><strong>ACTIVE</strong></td>
@else
<td><strong>INACTIVE</strong></td>
@endif

<td>{{ $formula->created_at }}</td>
<td>

<a class="btn btn-outline-info"  href="{{ route('formulas.edit',$formula->id) }}">Update</a>


</td>
</tr>
@endforeach
</table>
{!! $formulas->links() !!}
</body>
</html>