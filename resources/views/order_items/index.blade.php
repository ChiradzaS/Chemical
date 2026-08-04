<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Items</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

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
        .btn-create {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 18px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-create:hover { background: #0284c7; color: #fff; }

        .alert.alert-success.alert-dismissible {
            border-radius: 10px;
            padding: 14px 40px 14px 16px;
            font-size: .9rem;
            position: relative;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
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

        .page-body { padding: 20px; max-width: 1300px; margin: 0 auto; }

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
        .filter-actions { margin-left: auto; }
        .btn-search {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 22px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-search:hover { background: #0284c7; }

        .section-title {
            font-size: .95rem;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 4px 0 12px;
        }

        /* ── Items table ── */
        table.items-table { width: 100%; border-collapse: collapse; }
        table.items-table thead th {
            text-align: left;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            padding: 8px 10px;
            background: #f8fafc;
        }
        table.items-table tbody td {
            padding: 9px 10px;
            font-size: .85rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        table.items-table tbody tr:hover { background: #f0f9ff; }

        .status-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: .72rem;
            font-weight: 700;
            background: #e0f2fe;
            color: #075985;
        }
        .status-pill.unknown { background: #f1f5f9; color: #64748b; }
        .badge.badge-primary {
            background: #dbeafe;
            color: #1e40af;
            font-weight: 700;
            font-size: .7rem;
            padding: 4px 9px;
            border-radius: 99px;
        }

        .item-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .item-actions .btn { border-radius: 6px !important; font-weight: 600; }

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

        span.select2.select2-container.select2-container--classic { width: 100% !important; }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>Order Items</h1>
        <span class="badge">Orders</span>
    </div>
    <a class="btn-create" href="{{ route('orders.create') }}">Create New</a>
</div>

<div class="page-body">

@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success !</strong> {{ $message }}
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

{{-- ── Filters ── --}}
<div class="card">
    <div class="card-header"><span class="dot"></span> Filters</div>
    <div class="card-body">
        <form action="{{ route('order_items.index')}}" method="GET" class="filter-row">

            <div class="field">
                <label>From</label>
                <input type="date" name="fromDate" id="fromDate" class="form-control">
            </div>

            <div class="field">
                <label>To</label>
                <input type="date" name="toDate" id="toDate" class="form-control">
            </div>

            <div class="field" style="min-width:220px;">
                <label>Customer</label>
                <select name="customerId" id="customers" class="js-example-basic-single" style="width:100%;">
                    <option value="0 " disabled selected hidden>-- select customer name --</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="min-width:260px;">
                <label>Products</label>
                <select name="productId" id="productId" class="js-example-basic-single" style="width:100%;">
                    <option value="0" disabled selected hidden>-- select product name --</option>
                    @foreach($chemicalProducts as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field filter-actions">
                <button class="btn-search" value="query" name="action" type="submit">Search</button>
            </div>

        </form>
    </div>
</div>

<div class="section-title">Order List</div>

{{-- ── Items table ── --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <table class="items-table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Unit Type</th>
                    <th>Opening Qty</th>
                    <th>Outstanding</th>
                    <th>Date Created</th>
                    <th>Due Date</th>
                    <th>State</th>
                    <th></th>
                    <th width="400px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order_items as $order_item)
                @php
                    $tmpProduct = isset($order_item->productId) && isset($chemicalProducts[$order_item->productId])
                        ? $chemicalProducts[$order_item->productId]
                        : null;

                    $tmpUnittype = isset($order_item->unitId) && isset($containerSizes[$order_item->unitId])
                        ? $containerSizes[$order_item->unitId]
                        : null;

                    $tmpCustomer = isset($order_item->customerId) && isset($customers[$order_item->customerId])
                        ? $customers[$order_item->customerId]
                        : null;

                    $tmpstatus = isset($order_item->stateId) && isset($statustypes[$order_item->stateId])
                        ? $statustypes[$order_item->stateId]
                        : null;

                    $jobCard = \App\Models\JobCard::where('orderId', $order_item->id)->first();
                @endphp

                <tr>
                    <td>{{ $order_item->id }}</td>
                    <td>{{ $tmpCustomer->name ?? 'none' }}</td>
                    <td>{{ $tmpProduct->name ?? 'none' }}</td>
                    <td>{{ $tmpUnittype->name ?? 'none' }}</td>
                    <td>{{ $order_item->openningQNT }}</td>
                    <td>{{ $order_item->quantity }}</td>
                    <td>{{ substr($order_item->created_at, 0, 10) }}</td>
                    <td>{{ $order_item->dueDate }}</td>
                    <td>
                        <span class="status-pill {{ $tmpstatus ? '' : 'unknown' }}">{{ $tmpstatus->name ?? 'Unknown' }}</span>
                    </td>
                    <td>
                        @if($order_item->orderBy == 1)
                            <span class="badge badge-primary">Online</span>
                        @endif
                    </td>
                    <td>
                        <div class="item-actions">
                            <!-- <a class="btn btn-outline-secondary btn-sm" onclick="showSpinner()" href="{{ route('deliveries.create',['orderitem' => $order_item->id])}}">Delivery</a> -->

                            <div class="btn-group" role="group">
                                <form action="{{ route('actionorderitems.actionupdate',['order' => $order_item->id]) }}" method="GET">
                                    <button type="submit" class="btn btn-outline-success btn-sm">Update</button>
                                </form>

                                <form action="{{ route('actionorderitems.actiondel',['order' => $order_item->id]) }}" method="GET">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>{{-- end page-body --}}

<script>
    function showAlert() {
        alert("You cannot update an order already in progress");
    }
</script>

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