<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

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
            justify-content: space-between;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
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
        .topbar-actions { display: flex; gap: 10px; }
        .btn-plain {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 6px 18px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: color .15s, border-color .15s;
            display: inline-block;
        }
        .btn-plain:hover { color: #fff; border-color: #64748b; }
        .btn-primary {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 22px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background .15s;
        }
        .btn-primary:hover { background: #0284c7; color: #fff; }

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
        .page-body { padding: 20px; max-width: 1100px; margin: 0 auto; }

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
        .g3 { grid-template-columns: 1fr 1fr 1fr; }

        .field { display: flex; flex-direction: column; gap: 3px; }
        .field label {
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        input[type="text"], select {
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            padding: 5px 10px;
            font-size: .82rem;
            color: #1e293b;
            background: #fff;
            width: 100%;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,.12);
        }
        select[disabled] { background: #f1f5f9; color: #64748b; }

        .actions-row { display: flex; gap: 10px; margin: 4px 0 16px; }

        /* ── Table ── */
        table.order-items { width: 100%; border-collapse: collapse; }
        table.order-items thead th {
            text-align: left;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            padding: 8px 10px;
        }
        table.order-items tbody td {
            padding: 9px 10px;
            font-size: .85rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        table.order-items tbody tr:nth-child(even) { background: #f8fafc; }
        table.order-items tbody tr:hover { background: #f0f9ff; }

        .status-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: .72rem;
            font-weight: 700;
            background: #e0f2fe;
            color: #075985;
        }
        .status-pill.complete { background: #dcfce7; color: #166534; }

        .row-actions { display: flex; gap: 8px; }
        .row-actions .btn { border-radius: 6px !important; font-weight: 600; }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>Edit Order</h1>
        <span class="badge">Orders</span>
    </div>
</div>

@if(session('status'))
<div class="alert-success">{{ session('status') }}</div>
@endif

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<div class="page-body">

    {{-- Order details --}}
    <div class="card">
        <div class="card-header"><span class="dot"></span> Order Details</div>
        <div class="card-body">
            <div class="grid g3">
                <div class="field">
                    <label>Customer</label>
                    <select name="customerId" id="customers" class="js-example-basic-single" disabled>
                        @foreach($customers as $customer)
                            @foreach($orders as $order)
                                <option value="{{ $customer->id }}" @if($customer->id == $order['customerId']) selected @endif>{{ $customer->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Reference</label>
                    <input type="text" name="reference" value="{{ $orders[0]['reference'] }}" placeholder="Reference">
                    @error('reference')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Date Placed</label>
                    <input type="text" name="created_at" value="{{ $orders[0]['created_at'] }}">
                    @error('totalValue')
                    <div class="alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="actions-row">
        <a class="btn-primary" href="{{ route('order_items.create', ['ordersId' =>  $orders[0]['id'],'customer'=>  $orders[0]['customerId']]) }}">Add</a>
        <a class="btn-plain" href="{{ route('orders.index') }}">Back</a>
    </div>

    {{-- Order items --}}
    <div class="card">
        <div class="card-header"><span class="dot"></span> Order Items</div>
        <div class="card-body" style="padding:0;">
            <table class="order-items">
                <thead>
                    <tr>
                        <th>Created</th>
                        <th>Order Item No</th>
                        <th>Reference</th>
                        <th>Product Type</th>
                        <th>Unit Type</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>State</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderitems as $orderitem)
                        @php
                            $tmpProduct = isset($orderitem['productId']) ? $chemicalProducts[$orderitem['productId']] ?? 'none' : 'none';
                            $tmpUnittype = isset($orderitem['unitId']) ? $containerSizes[$orderitem['unitId']] ?? 'none' : 'none';
                            $tmpstatus = isset($orderitem['stateId']) ? $statusTypes[$orderitem['stateId']] ?? 'none' : 'none';
                        @endphp
                        <tr>
                            <td>{{ $orderitem['created_at'] }}</td>
                            <td>{{ $orderitem['id'] }}</td>
                            <td>{{ $orderitem['reference'] }}</td>
                            <td>{{ $tmpProduct->name ?? 'none' }}</td>
                            <td>{{ $tmpUnittype->name ?? 'none' }}</td>
                            <td>{{ $orderitem['quantity'] ?? 'none' }}</td>
                            <td>{{ $orderitem['price'] ?? '' }}</td>
                            <td>
                                @if(is_object($tmpstatus) && $tmpstatus->name)
                                    <span class="status-pill {{ $tmpstatus->name == 'Complete' ? 'complete' : '' }}">{{ $tmpstatus->name }}</span>
                                @endif
                            </td>

                            <td>{{ is_object($tmpstatus) ? $tmpstatus->name : '' }}</td>
                            @if(is_object($tmpstatus) && $tmpstatus->name == 'Complete')
                            <td><button type="button" class="btn btn-success btn-sm">Complete...</button></td>
                            @else
                            @endif

                            <td>
                                <div class="row-actions">
                                    <form action="{{ route('actionorderitems.actionupdate1',['order' =>  $orderitem['id']]) }}" method="GET">
                                        <button type="submit" class="btn btn-outline-info btn-sm">Update</button>
                                    </form>

                                    <form action="{{ route('actionorderitems.actiondel2',['order' =>  $orderitem['id']]) }}" method="GET">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete')">Delete</button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>