<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script type='text/javascript'>
    $(document).ready(function(){

    // Product data (container size + price) is already available in
    // $chemicalProducts when this page renders, so it's embedded as
    // data-* attributes on each <option> below — no AJAX round trip
    // needed to fetch it after the fact.
    $("#productId").change(function(){

        var selected = $(this).find('option:selected');

        var containerSizeId = selected.data('container-size-id');
        var price            = selected.data('price');

        if (containerSizeId === undefined || containerSizeId === '' || containerSizeId === null) {
            console.warn('setProduct: no container_size_id on selected product option', selected.val());
        }

        var $unitSelect = $('#unitId');
        $unitSelect.val(String(containerSizeId));

        if ($unitSelect.val() !== String(containerSizeId)) {
            console.warn('setProduct: unitId select has no matching <option> for value', containerSizeId);
        }

        $unitSelect.trigger('change');

        document.getElementById("price").value = (price !== undefined && price !== null && price !== '')
            ? price
            : '';
    });

    });
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
        .page-body { padding: 20px; max-width: 900px; margin: 0 auto; }

        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
            margin-bottom: 16px;
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

        .actions-row { display: flex; justify-content: flex-end; }
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

        /* ── Spinner ── */
        .spinner-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .spinner {
            width: 17.6px;
            height: 17.6px;
            border-radius: 17.6px;
            box-shadow: 44px 0px 0 0 rgba(0, 0, 0, 0.2), 35.6px 26px 0 0 rgba(0, 0, 0, 0.4), 13.64px 41.8px 0 0 rgba(0, 0, 0, 0.6), -13.64px 41.8px 0 0 rgba(0, 0, 0, 0.8), -35.6px 26px 0 0 #000000;
            animation: spinner-b87k6z 1.4s infinite linear;
        }
        @keyframes spinner-b87k6z { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <h1>Create Order</h1>
    <span class="badge">Orders</span>
</div>

@if(session('status'))
<div class="alert-success">{{ session('status') }}</div>
@endif

<form action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<div class="page-body">

    {{-- Order Details --}}
    <div class="card">
        <div class="card-header"><span class="dot"></span> Order Details</div>
        <div class="card-body">
            <div class="grid g2">
                <div class="field" style="grid-column: span 2;">
                    <label>Customer</label>
                    <select name="customerId" id="customers" class="js-example-basic-single">
                        <option value="" disabled selected hidden>-- select customer name --</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field" style="grid-column: span 2;">
                    <label>Reference</label>
                    <input type="text" name="reference" placeholder="Reference">
                    @error('reference')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Due Date</label>
                    <input type="date" id="dueDate" name="dueDate" placeholder="ref">
                    @error('dueDate')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Date order was placed</label>
                    <input type="date" id="datePlaced" name="datePlaced" placeholder="ref">
                    @error('dueDate')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Order Item [Product] --}}
    <div class="card">
        <div class="card-header"><span class="dot"></span> Order Item [Product]</div>
        <div class="card-body">
            <div class="grid g2">
                <div class="field" style="grid-column: span 2;">
                    <label>Product</label>
                    <select id="productId" name="productId" class="js-example-basic-single">
                        <option value="" disabled selected hidden>-- select product name --</option>
                        @foreach($chemicalProducts as $product)
                        <option value="{{ $product->id }}" data-container-size-id="{{ $product->container_size_id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Unit Type</label>
                    <select id="unitId" name="unitId">
                        <option value="" disabled selected hidden>-- select unit type --</option>
                        @foreach($containerSizes as $unittype)
                        <option value="{{ $unittype->id }}">{{ $unittype->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Price</label>
                    <input id="price" name="price" placeholder="price">
                    @error('price')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Quantity</label>
                    <input name="quantity" placeholder="Quantity">
                    @error('quantity')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Total Cost</label>
                    <input name="totalPrice">
                    @error('totalPrice')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Reference</label>
                    <input id="referenceItem" name="reference_item" value="null">
                    @error('price')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Due Date</label>
                    <input type="date" id="dueDateItem" name="dueDate">
                    @error('dueDate')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="actions-row" style="margin-top:18px;">
                <button type="submit" onclick="showSpinner()" class="btn-save">Save</button>
            </div>
        </div>
    </div>

</div>
</form>

<!-- Spinner Container -->
<div id="spinnerContainer" class="spinner-container">
    <div class="spinner"></div>
</div>

<script>
    function showSpinner() {
        document.getElementById('spinnerContainer').style.display = 'block';
        setTimeout(function () {
            document.getElementById('spinnerContainer').style.display = 'none';
        }, 3000);
    }
</script>
</body>
</html>