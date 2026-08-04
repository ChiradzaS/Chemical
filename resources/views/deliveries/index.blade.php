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


<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h3>Add New delivery</h3>
<br>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>

<table class="table table-striped" >
<tr>
    
<th  scope="col" width="20%"> Customer</th>
<th  scope="col"> Product</th>
<th  scope="col"> Unit</th>
<th  scope="col"> Quantity</th>
<th  scope="col"> Driver</th>
<th  scope="col"  width="10%"> Vehicle</th>
<th  scope="col" width="20%"> Address</th>
<th  scope="col"> Invoice</th>
<th  scope="col"> Date</th>

</tr>
@foreach ($deliveries as $delivery)
@php
    $tmpUnittype = $unittypes[$delivery->unitId] ?? null;
    $tmpCustomer = $customers[$delivery->customerId] ?? null;
    $tmpP = $products[$delivery->productId] ?? null;
    $tmdriver = $drivers[$delivery->driver] ?? null;
    $tmpvehicle = $vehicles[$delivery->vehicleReg] ?? null;
@endphp
<tr>
<td>{{ $tmpCustomer?->name ?? 'null' }}</td>
<td>{{ $tmpP?->name ?? 'null' }}</td>
<td>{{ $tmpUnittype?->name ?? 'null' }}</td>
<td>{{ $delivery->qnt }}</td>
<td>{{ $tmdriver?->name ?? 'null' }}</td>
<td>{{ $tmpvehicle?->name ?? 'null' }}</td>
<td>{{ $delivery->addressId }}</td>
<td>{{ $delivery->invoiceNo }}</td>
<td>{{ $delivery->created_at }}</td>
</tr>
@endforeach
</table>
{!! $deliveries->links() !!}
</body>
</html>