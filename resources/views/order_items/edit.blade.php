<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Item</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script type='text/javascript'>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function(){

    $("#productId").change(function(){

    var cboProduct = document.getElementById("productId");
    var aproductId = cboProduct.options[cboProduct.selectedIndex].value;

    var productid = Number(aproductId);

    if(productid > 0){

    $.ajax({
                url: "{{ route('getProductbyid') }}",

                type: 'post',

                data: {_token: CSRF_TOKEN, productid: productid},

                dataType: 'json',

    success: function(response){

         setProduct(response);

    }


    });
    }


    });



    });

    function setProduct(response){
                      if(response['data'] != null){
                         len = response['data'].length;
                      }

                    level = -9;
                    level = response['packagingLevel'];


                    if (level > 0) {
                      if (len > 0) {
                        for(var i=0; i<len; i++){

                           var id = response['data'][i].id;
                           var name = response['data'][i].name;
                           var defaultSellingPice = response['data'][i].defaultSellingPice;
                           var actualSellingPrice = response['data'][i].actualSellingPrice;
                           var unitTypeId = response['data'][i].unitPackId;


                           document.getElementById("unitId").value = "" + unitTypeId;
                           document.getElementById("price").value = "" + actualSellingPrice;

                        }
                      }
                    } else {
                      if (len > 0) {
                        for(var i=0; i<len; i++){
                           var unitTypeId = response['data'][i].unitTypeId;
                           var defaultSellingPice = response['data'][i].defaultSellingPice;
                           var actualSellingPrice = response['data'][i].actualSellingPrice;



                           document.getElementById("unitId").value = "" + unitTypeId;
                           document.getElementById("price").value = "" + actualSellingPrice;

                        }
                      }
                    }
                  }
    </script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }

        /* ── Top bar ── */
        .topbar {
            background: #0f172a;
            color: #fff;
            padding: 0 24px;
            height: 52px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar h1 { font-size: 1rem; font-weight: 700; color: #fff; }
        .topbar .badge {
            background: #0ea5e9;
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 2px 9px;
            border-radius: 99px;
        }

        .alert-success {
            background: #dcfce7;
            border-bottom: 1px solid #bbf7d0;
            color: #166534;
            padding: 10px 24px;
            font-size: .85rem;
        }
        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 5px;
            padding: 4px 8px;
            font-size: .75rem;
            margin-top: 3px;
        }

        /* ── Body ── */
        .page-body { padding: 20px; max-width: 700px; margin: 0 auto; }

        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .card-header {
            background: #0f172a;
            color: #fff;
            padding: 8px 16px;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .card-header .dot { width: 6px; height: 6px; background: #0ea5e9; border-radius: 50%; flex-shrink: 0; }
        .card-body { padding: 16px; }

        .grid { display: grid; gap: 14px; }
        .g2 { grid-template-columns: 1fr 1fr; }

        .field { display: flex; flex-direction: column; gap: 3px; }
        .field label {
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        input[type="text"], input[type="date"], input:not([type]), select {
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            padding: 6px 10px;
            font-size: .85rem;
            color: #1e293b;
            background: #fff;
            width: 100%;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,.12);
        }
        input[readonly] { background: #f1f5f9; color: #64748b; }

        .actions-row { display: flex; justify-content: flex-end; margin-top: 18px; }
        .btn-save {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 28px;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-save:hover { background: #0284c7; }

        span.select2.select2-container.select2-container--classic { width: 100% !important; }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <h1>Update Order Item</h1>
    <span class="badge">Orders</span>
</div>

@if(session('status'))
<div class="alert-success">{{ session('status') }}</div>
@endif

<form action="{{ route('order_items.update',$order_items[0]['id']) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<input type="text" name="ordersId" value="{{ $order_items[0]['ordersId']}}" hidden>

<div class="page-body">
    <div class="card">
        <div class="card-header"><span class="dot"></span> Order Item Details</div>
        <div class="card-body">
            <div class="grid g2">

                <div class="field" style="grid-column: span 2;">
                    <label>Product</label>
                    <select id="productId" name="productId" class="js-example-basic-single">
                        @foreach($chemicalProducts as $product)
                        <option value="{{ $product->id }}" @if ($product->id==$order_items[0]['productId'] )selected @endif>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Unit Type</label>
                    <select id="unitId" name="unitId">
                        @foreach($containerSizes as $unittype)
                        <option value="{{$unittype->id}}" @if($unittype->id==$order_items[0]['unitId'] ) selected @endif>{{$unittype->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Quantity</label>
                    <input name="quantity" value="{{ $order_items[0]['quantity'] }}">
                    @error('quantity')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Price</label>
                    <input id="price" name="price" value="{{ $order_items[0]['price'] }}" readonly>
                    @error('price')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Reference</label>
                    <input id="reference" name="reference" value="{{$order_items[0]['reference']}}">
                    @error('price')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Due Date</label>
                    <input type="date" id="dueDate" name="dueDate" value="{{$order_items[0]['dueDate'] }}">
                    @error('dueDate')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="actions-row">
                <button type="submit" class="btn-save">Save</button>
            </div>
        </div>
    </div>
</div>

</form>

</body>
</html>