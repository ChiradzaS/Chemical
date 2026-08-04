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

<title>Stock List</title>

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .table-sm td, .table-sm th {
        padding: 0.3rem;
        font-size: 0.875rem;
    }
    .qty-in {
        color: #16a34a;
        font-weight: 700;
    }
    .qty-out {
        color: #dc2626;
        font-weight: 700;
    }
</style>

</head>
<body>
<div>
<br>
</div>
<div class="container-fluid mt-2">
<div class="row">
    <div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Stock Transaction Details</h2>
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif

<table class="table table-striped table-sm table-bordered">
<thead>
<tr>
<th scope="col">Transaction-Type</th>

<th scope="col">Quantity</th>
<th scope="col">Date</th>
</tr>
</thead>
<tbody>
@foreach ($stockDetails as $stockDetail)
@php
    // Adding stock -> green with a plus sign, subtracting stock -> red with a minus sign
    $isAddition = $stockDetail->qnt >= 0;
    $qtyClass   = $isAddition ? 'qty-in' : 'qty-out';
    $qtySign    = $isAddition ? '+' : '-';
@endphp
<tr>
    <td>{{ $doctypes[$stockDetail->docType]->name }}</td>

    <td class="{{ $qtyClass }}">{{ $qtySign }}{{ abs($stockDetail->qnt) }}</td>
    <td>{{ \Carbon\Carbon::parse($stockDetail->created_at)->format('Y-m-d H:i') }}</td>
</tr>
@endforeach
</tbody>
</table>

<br>
<button type="button" onclick="javascript:window.history.back();" class="btn btn-primary btn-sm">Back</button> 
</div>
</body>
</html>