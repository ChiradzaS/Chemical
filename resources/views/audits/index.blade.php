<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">


<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<style>
    h3{
        text-align: center;
    }
 </style>
</html>
<h3>AUDIT RECORDS</h3>
</div>
<br>
<div class="pull-right mb-2">
</div>
</div>
</div>
<button class="btn btn-outline-info"  onclick="javascript:window.history.back();">Go Back</button>
    <br>
<table>
<table class="table table-striped" >
<tr>
<th  scope="col"> id</th>
<th  scope="col"> Document Id</th>
<th  scope="col"> Document Type</th>
<th  scope="col"> Document Status</th>
<th  scope="col"> Action</th>
<th  scope="col"> created_at </th>

</tr>

@foreach ($audits as $audit)

<tr>
<td>{{ $audit->id }}</td>
<td>{{ $audit->docId }}</td>
<td>{{ $audit->docType }}</td>
<td>{{ $audit->status }}</td>
<td>{{ $audit->action }}</td>
<td>{{ $audit->created_at }}</td>

</tr>
@endforeach
</table>
{!! $audits->links() !!}
</body>
</html>