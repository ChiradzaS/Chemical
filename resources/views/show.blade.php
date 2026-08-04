<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">






<!--------------------------------------------------------------------------------------------------------------------------->
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{asset('public/style/table.css')}}" >
    <title>Document</title>
</head>
<body>
@include('view')
<div class="table-container">
<br>
<br>
<br>
<h3>List fo all companies </h3>
<br>
<a class="btn btn-primary" href="{{ route('companies.index') }}" enctype="multipart/form-data"> Back</a>
<br>
<table class="table table-bordered  width="100%">
    <thead >
        <th  scope="col"> ID</th>
        <th  scope="col"> Name</th>
        <th  scope="col"> Email</th>
        <th  scope="col"> Address</th>
        <th  scope="col"> Time_created</th>
        <th  scope="col"> Time_updated</th>

    </thead>
    <tbody>
        @foreach($companies as $company)
    <tr>
        <td>{{$company['id']}}</td>
        <td>{{$company['name']}}</td>
        <td>{{$company['email']}}</td>
        <td>{{$company['address']}}</td>
        <td>{{$company['created_at']}}</td>
        <td>{{$company['updated_at']}}</td>

    </tr>
    @endforeach
</div>
    </tbody>
</table>
</body>
</html>
