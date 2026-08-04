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
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">


<style>
        tr {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2); /* Adjust the values as needed */
        transition: box-shadow 0.3s ease;
    }
</style>

<h3>Running Terminal Per Machine List</h3>

<br>
<table class="table table-striped" >


<tr>


<th  scope="col"> Machine</th>
<th  scope="col"> Operator</th>
<th  scope="col"> Terminal</th>
<th  scope="col"> IP address</th>
<th  scope="col"> shift</th>
<th  scope="col"> JobCard</th>
<th  scope="col"> Date</th>

</tr>


@foreach ($terminals as $terminal)
@php $tmpuser = $user[$terminal->userId]??''; @endphp
@php $tmpMachinetype = $machinetypes[$terminal->machineId]??''; @endphp
@php $tmpShifttype = $shifttypes[$terminal->shiftId]??''; @endphp



<td>{{  $tmpMachinetype->name??''}}</td>
<td>{{ $tmpuser->name ??''  }}</td>
<td>{{ $terminal->terminal }}</td>
<td>{{ $terminal->terminalIpAddress}}</td>
<td>{{ $tmpShifttype->name??''}}</td>
<td>{{ $terminal->jobCardId }}</td>
<td>{{ $terminal->created_at}}</td>






</tr>
@endforeach
</table>

</body>
</html>