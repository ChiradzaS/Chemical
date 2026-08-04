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
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<br>
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success! </strong>{{ $message }}
</div>
@endif


@if ($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Failed!</strong> {{ $message }}
</div>
@endif


<script>
  function closeNotification() {
    var alertElement = document.querySelector('.alert');
    if (alertElement) {
        alertElement.style.display = 'none';

    }

    
}


</script>


<style>
  
    .alert.alert-success.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }

    .alert.alert-danger.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }
</style>
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Invoice List</th>
</tr>
</thead>
</table>

<br>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<form action="{{ route('invoices.index')}}" method="GET">
<table class="table table-striped" >

<style>
    .table table-striped{
        width: 10%;
}
</style>

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>



<td width="200px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    
    <strong>Customer name:</strong>
  </div>&nbsp
  <select name="customerId" id="customerId"   class="js-example-basic-single"   placeholder="-- Select Customer --">
<option value="" disabled selected hidden>-- select customer name --</option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}"  >{{ $customer->name }}</option>
@endforeach
</select>
</div>
</td>








<td width="100px">
 <button type="submit" value="query" name="action" class="form-control  btn-dark" >Search</button>
</td>


</form>
<table class="table table-striped" >
<tr>
<th  scope="col"> Invoice No</th>
<th  scope="col"> Customer</th>
<th  scope="col"> Invoice Reference</th>
<th  scope="col"> Total Value</th>
<th  scope="col"> Status</th>
<th  scope="col" width="200px"> Action</th>
</tr>
@foreach ($invoices as $invoice)
@php $tmpCustomer = $customers[$invoice->customerId]; @endphp
<tr>
<td>{{ $invoice->id }}</td>
<td>{{ $tmpCustomer->name }}</td>
<td>{{ $invoice->reference }}</td>
<td>{{ $invoice->totalValue }}</td>
<td>{{ $invoice->status }}</td>
<td>
<form action="{{ route('invoices.destroy',$invoice->id) }}" method="Post">
<a class="btn btn-outline-info" href="{{ route('invoices.edit',$invoice->id) }}">View</a>
@csrf
@method('DELETE')
<button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
</form>
</td>
</tr>
@endforeach
</table>
{!! $invoices->links() !!}
</body>
</html>