<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>

    <!------------------------------------ Local jars in public folder --------------------------------------------------------->
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>
    <script src="{{ asset('public/js/script.js') }}"></script>
    <!--------------------------------------------------------------------------------------------------------------------------->

    <link rel="stylesheet" href="{{ asset('style/style.css') }}">

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

        .page-body { padding: 20px 32px; width: 100%; }

        /* ── Alerts ── */
        .alert.alert-success.alert-dismissible,
        .alert.alert-danger.alert-dismissible {
            border-radius: 10px;
            padding: 14px 40px 14px 16px;
            font-size: .9rem;
            position: relative;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }
        .alert-success { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
        .alert-danger { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
        .alert .close {
            position: absolute;
            top: 8px;
            right: 12px;
            background: none;
            border: none;
            font-size: 1.1rem;
            line-height: 1;
            color: inherit;
            opacity: .6;
            cursor: pointer;
        }
        .alert .close:hover { opacity: 1; }

        /* ── Cards ── */
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

        /* ── Filter form ── */
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 14px;
        }
        .filter-row .field { display: flex; flex-direction: column; gap: 3px; }
        .filter-row .field label {
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .filter-row select,
        .filter-row input[type="date"] {
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            padding: 5px 10px;
            font-size: .82rem;
            min-width: 180px;
        }
        .filter-row select:focus,
        .filter-row input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,.12);
        }
        .filter-actions { display: flex; gap: 10px; margin-left: auto; }

        .btn-primary-pill {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 20px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-primary-pill:hover { background: #0284c7; }
        .btn-plain-pill {
            background: transparent;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 20px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: color .15s, border-color .15s;
        }
        .btn-plain-pill:hover { color: #0f172a; border-color: #94a3b8; }

        .section-title {
            font-size: .95rem;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 4px 0 12px;
        }

        /* ── Orders table ── */
        table.orders-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }

        .group-header td {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 10px 12px;
            vertical-align: middle;
        }
        .group-header td:first-child { border-radius: 8px 0 0 8px; }
        .group-header td:last-child { border-radius: 0 8px 8px 0; }
        .group-header .customer-name { font-size: 1rem; font-weight: 800; letter-spacing: .02em; }
        .group-header .created-date { font-size: .78rem; color: #cbd5e1; font-weight: 600; }

        .header-btn {
            background: rgba(255,255,255,.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 6px;
            padding: 4px 12px;
            font-size: .72rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .header-btn:hover { background: rgba(255,255,255,.22); }
        .header-btn.danger { border-color: rgba(248,113,113,.5); }
        .header-btn.danger:hover { background: rgba(248,113,113,.25); }

        tr.item-row td {
            background: #fff;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 12px;
            font-size: .85rem;
            vertical-align: middle;
        }
        tr.item-row td:first-child { border-left: 1px solid #f1f5f9; }
        tr.item-row td:last-child { border-right: 1px solid #f1f5f9; }

        .product-name { font-weight: 700; color: #1e293b; }
        .unit-name { color: #64748b; font-weight: 500; }

        .badge.badge-primary {
            background: #e0f2fe;
            color: #075985;
            font-weight: 700;
            font-size: .7rem;
            padding: 4px 9px;
            border-radius: 99px;
        }

        .inline-content { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .stat-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; font-weight: 700; margin-right: 3px; }
        .stat-value { font-size: .85rem; }

        .item-actions .btn { border-radius: 6px !important; font-weight: 600; }

        .large-row { color: purple; height: 50px; font-size: 20px; padding: 10px; }

        .no-orders-alert {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 10px;
            padding: 16px;
            font-size: .9rem;
        }

        /* Select2 tweak */
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

        /* ── Print ── */
        @media print {
            .none { display: none !important; }
            .no-print, .no-print .select2-container, .no-print .select2-container * { display: none !important; }
            .topbar { background: #fff !important; color: #000 !important; border-bottom: 2px solid #000; }
            .topbar h1 { color: #000 !important; }
            .card { box-shadow: none; border: 1px solid #000; }
            .group-header td { background: #fff !important; color: #000 !important; border-top: 1px solid #000; border-bottom: 1px solid #000; }
            .group-header .created-date { color: #000 !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <h1>Orders</h1>
    <span class="badge">Orders</span>
</div>

<div class="page-body">

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

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

{{-- ── Filters ── --}}
<div class="card none">
    <div class="card-header"><span class="dot"></span> Filters</div>
    <div class="card-body">
        <form action="{{ route('orders.index')}}" method="GET" class="filter-row">
            <div class="field" style="flex:1; min-width:220px;">
                <label>Customer</label>
                <select name="customerId" id="customerId" class="js-example-basic-single" style="width:100%;">
                    <option value="" {{ $customerId == '' ? 'selected' : '' }}>--All Customers--</option>
                    @foreach($customers as $customer)
                        <option value="{{$customer->id }}" @if($customerId == $customer->id ) selected @endif>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label>From</label>
                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="form-control">
            </div>

            <div class="field">
                <label>To</label>
                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="form-control">
            </div>

            <div class="filter-actions">
                <button class="btn-primary-pill" value="query" name="action" type="submit">Search</button>
                <button class="btn-plain-pill" value="query" name="action" onclick="print()">Print</button>
            </div>
        </form>
    </div>
</div>

<div class="section-title">Orders List</div>

<table class="orders-table">

@php
    $lastOrderId = null;
@endphp

@if(isset($orders) && !empty($orders))

@foreach ($orders as $order)

@php $tmpProduct = $chemicalProducts[$order['order_items_productId']]??''; @endphp
@php $tmpUnittype = $containerSizes[$order['order_items_unitId']] ??''; @endphp
@php $tmpCustomer = $customers[$order['orders_customerId']] ?? 'N/A'; @endphp
@php $tmpstatus = $statustypes[$order['order_items_stateId']] ?? ''; @endphp
@php  $jobCard = \App\Models\JobCard::where('orderId', $order['orders_id'])->first();   @endphp

    @if ($order['orders_id'] != $lastOrderId)
        @if (!is_null($lastOrderId))
            </tbody>
        @endif

        <thead>
        <tr class="group-header">
        <td colspan='3'>
            <span class="customer-name">{{ $tmpCustomer->name }}</span>
        </td>
        <td><span class="created-date">{{ $order['orders_created_at'] }}</span></td>

        <td>
            <div class="inline-content">
                <form action="{{ route('actionorders.actiondelete',['order' => $order['orders_id'] ]) }}" method="GET">
                    <button type="submit" style="display: none;" class="btn btn-light btn-sm none">Delete</button>
                </form>

                <form action="{{ route('actionorders.actionupdate',['order' => $order['orders_id']]) }}" method="GET">
                    <button type="submit" class="header-btn none">Add Items</button>
                </form>

                <!-- <form action="{{ route('actionorders.actionview',['order' => $order['orders_id']]) }}" method="GET">
                    <button type="submit" class="header-btn none">View</button>
                </form> -->

                <form action="{{ route('actionorders.actiondel',['order' => $order['orders_id']]) }}" method="GET">
                    <button type="submit" class="header-btn danger none" onclick="return confirm('Are you sure you want to delete')">Delete</button>
                </form>
            </div>
        </td>
        <td></td>
        <td></td>
        <td></td>

        </tr>
        </thead>
        <tbody>
        @php
            $lastOrderId = $order['orders_id'];
        @endphp
    @endif

    <tr class="item-row">

    <td colspan="4"></td>

        <td>
            <span class="product-name">
            @if(isset($tmpProduct) && $tmpProduct)
                {{ $tmpProduct->name ?? 'Product Not Found' }}
            @else
                Product Not Available
            @endif
            </span>
            <span class="unit-name">
            @if(isset($tmpProduct) && isset($tmpUnittype) && $tmpUnittype)
                --- {{ $tmpUnittype->name ?? 'Unit Type Not Found' }}
            @else
                --- Unit Type Not Available
            @endif
            </span>
        </td>

        <td>
            @if(  $order['order_items_orderBy']== 1)
                <span class="badge badge-primary">Online</span>
            @endif
        </td>

        <td>
            <div class="inline-content">
                <span class="stat-label">Quantity</span>
                <span class="stat-value" style="color: {{ $order['order_items_openningQNT'] < 0 ? 'red' : 'inherit' }};">
                    <strong>{{ $order['order_items_openningQNT'] }}</strong>
                </span>

                @php
                    $delivered = $order['order_items_openningQNT'] - $order['order_items_quantity'];
                @endphp
                <span class="stat-label">Delivered</span>
                <span class="stat-value" style="color: {{ $delivered < 0 ? 'red' : 'inherit' }};">
                    <strong>{{ $delivered }}</strong>
                </span>

                <span class="stat-label">Manufactured</span>
                <span class="stat-value" style="color: {{ $order['order_items_quantity'] < 0 ? 'red' : 'inherit' }};">
                    <strong>{{ $order['order_items_manufactured'] }}</strong>
                </span>
            </div>

        <td class="item-actions">
            <div class="inline-content">
                <form action="{{ route('actionorders.actiondelete',['order' => $order['orders_id'] ]) }}" method="GET">
                    <button type="submit" style="display: none;" class="btn btn-light btn-sm none">Delete</button>
                </form>

                <div class="btn-group" role="group">
                <form action="{{ route('actionorderitems.actionupdate',['order' => $order['order_items_id']]) }}" method="GET">
                    <button type="submit" class="btn btn-outline-success btn-sm none">Update</button>
                    <!-- <a class="btn btn-outline-secondary btn-sm none" onclick="showSpinner()" href="{{ route('deliveries.create',['orderitem' => $order['order_items_id']])}}">Delivery</a>
                    @if( is_null ($order['order_items_manufactured'] ))

                    <a class="btn btn-outline-info btn-sm none" onclick="showSpinner()" href="job_cards/create?value={{$order['order_items_productId']}}&value1={{$order['orders_customerId']}}&value2={{$order['order_items_quantity']}}&value3={{$order['order_items_id']}}"> Create Jobcard</a>

                    @else

                    <a class="btn btn-outline-info btn-sm none" onclick="showSpinner()" href="job_cards?value={{$order['order_items_productId']}}&value1={{$order['orders_customerId']}}&value2={{$order['order_items_quantity']}}&value3={{$order['order_items_id']}}">View Jobcard&nbsp;</a>

                    @endif -->
                </form>
                &nbsp;

                <form action="{{ route('actionorderitems.actiondel',['order' => $order['order_items_id']]) }}" method="GET">
                    <button type="submit" class="btn btn-outline-danger btn-sm none">Delete</button>
                </form>
                </div>
            </div>
        </td>

    </tr>
@endforeach

@if (!is_null($lastOrderId))
    </tbody>
@endif

@else

    <tr><td>
        <div class="no-orders-alert">
            <strong>No orders data available!</strong> {{ $message ?? '' }}
        </div>
    </td></tr>

@endif

</table>

</div>{{-- end page-body --}}

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