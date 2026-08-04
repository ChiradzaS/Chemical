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

</head>
@php Log::info(" Body 1 Edit --- invoices ------------------------------------------- : ");  @endphp
<body>
{{-- @include('view') --}}
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
    <br>
<h2>Invoice Details</h2>
</div>
<br>
<div class="pull-right">
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('invoices.update',$invoice->id) }}" method="POST" enctype="multipart/form-data"  onsubmit="return validateForm()">
@csrf
@method('PUT')


<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>



<style>
    span.select2.select2-container.select2-container--classic{
        width: 100% !important;
    }
</style>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <table class="table">
                <tr>
                    <!-- Customer Dropdown -->
                    <td>
                        <strong>Customer:</strong>
                        <select name="customerId" id="customers" class="js-example-basic-single" placeholder="-- Select Customer --">
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @if($customer->id == $invoice->customerId) selected @endif>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </td>

                    <!-- Reference Input -->
                    <td>
                        <strong>Reference:</strong>
                        <input type="text" name="reference" class="form-control form-control-sm" value="{{ $invoice->reference }}" placeholder="Reference">
                        @error('reference')
                            <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </td>

                    <!-- Other Input -->
                    <td>
                        <strong>Other:</strong>
                        <input type="text" name="other" class="form-control form-control-sm" value="{{ $invoice->other }}" placeholder="Other">
                        @error('other')
                            <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </td>

                    <!-- Total Value Input -->
                    <td>
                        <strong>Total Value:</strong>
                        <input type="text" name="totalValue" class="form-control form-control-sm" value="{{ $total }}" placeholder="Total Value">
                        @error('totalValue')
                            <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

@php
    Log::info(" yyyyy 1 Edit --- invoice ------------------------------------------- : ");
@endphp

<br>
<div>
<!-- <button type="submit" class="btn btn-outline-info">Save</button>&nbsp;
<a class="btn btn-outline-info" href="{{ route('invoice_items.create', ['invoicesId' => $invoice->id]) }}">Add</a> -->

</div>


</form>
</div>
<br>

@php Log::info(" Style 1 Edit --- invoices ------------------------------------------- : ");  @endphp

<style>
    h3{
        text-align: center;
    }


 </style>





<h3>Invoice items</h3>
<table class="table table-striped" >
    <tr>
        <th  scope="col"> ID no</th>
        <th  scope="col"> Product Type</th>
        <th  scope="col"> Unit Type</th>
        <th  scope="col"> Quantity</th>
        <th  scope="col"> Price</th>
        <th  scope="col"> Discount</th>
        <th  scope="col"> Tax (15%)</th>
        <th  scope="col"> Total Cost</th>
       
        <th  scope="col" width="200px"> Action</th>
        </tr>
        @php Log::info(" Style 2 Edit --- invoices ------------------------------------------- : ");  @endphp

        @foreach ($invoiceitems as $invoiceitem)
        <div class="invoiceitem">
        @php $tmpProduct = $porducts[$invoiceitem->productId]; @endphp
        @php $tmpUnittype = $unittypes[$invoiceitem->unitId]; @endphp       
        @php Log::info(" Style 3 Edit --- invoices ------------------------------------------- : ");  @endphp
        <tr>
  

        <td>{{ $invoiceitem->id }}</td>
     
        <td>{{ $tmpProduct->name }}</td>
        <td>{{ $tmpUnittype->name }}</td>
        <td>{{ $invoiceitem->quantity }}</td>
        <td>{{ $invoiceitem->price }}</td>
        <td>{{ $invoiceitem->Discount}}</td>
        <td>{{ $invoiceitem->vatAmnt}}</td>
        <td><input type="text" name="totalPrice"  id="totalPrice" class="form-control form-control-sm" value="R {{$invoiceitem->totalPrice }}" onchange="calculate()" placeholder="0.00 "readonly></td>
       
    <td>
        </div>
    <!-- <form action="{{ route('invoice_items.destroy',$invoiceitem->id) }}" method="Post">
    <a class="btn btn-outline-info" href="{{ route('invoice_items.edit',$invoiceitem->id) }}">Update</a>
    @csrf
    @method('DELETE') 
    <button type="submit" class="btn btn-outline-info" onclick="return confirm('Are you sure you want to delete')">Delete</button>
    </form>
    @endforeach -->
    <tr>
        <th  scope="col"></th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col">Sub Total (excl VAT)</th>
        <th  scope="col"> <input type="text" name="totalValue"  id="totalValue" class="form-control form-control-sm" value="R {{$totalexclVAT }}" readonly></th>
       
        <th  scope="col" width="200px"></th>
        </tr>
    <tr>
        <th  scope="col"></th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col">VAT (15%)</th>
        <th  scope="col"> <input type="text" name="totalValue"  id="totalValue" class="form-control form-control-sm" value="R {{  $totalVat}}" readonly></th>
       
        <th  scope="col" width="200px"></th>
        </tr>
        <script>
            .btn {
           min-width: 400px;
}
       </script>
    <tr>
        <th  scope="col"></th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col">Total (incl VAT)</th>
        <th  scope="col"> <input type="text" name="totalValue"  id="totalValue" class="form-control form-control-sm" value="{{ $total }}" readonly></th>
     
        <th  scope="col" width="200px"></th>
        </tr>
        <tr>
        <th  scope="col"></th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"> </th>
        <th  scope="col"></th>
        <th  scope="col"> <a class="btn btn-dark" href="{{ route('index.index', ['invoiceId' =>  $invoice->id,'total' => $total,'prntReport' => 'INVOICE_BY_INVOICEID']) }}">&emsp;&emsp;&emsp;&emsp;Print&emsp;&emsp;&emsp;&emsp;</a></th>
       
        <th  scope="col" width="200px"></th>
        </tr>
  

</body>
</html>