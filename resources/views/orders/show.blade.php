<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order</title>

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
            transition: color .15s, border-color .15s;
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
            transition: background .15s;
        }
        .btn-primary:hover { background: #0284c7; }

        .alert-success {
            background: #dcfce7;
            border-bottom: 1px solid #bbf7d0;
            color: #166534;
            padding: 10px 24px;
            font-size: .85rem;
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

        /* Customer readout */
        .customer-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
        }

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

        .empty-row td {
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            padding: 18px;
        }

        /* ── Print ── */
        @media print {
            .none { display: none !important; }
            .topbar { background: #fff !important; color: #000 !important; border-bottom: 2px solid #000; }
            .topbar h1 { color: #000 !important; }
            .card { box-shadow: none; border: 1px solid #000; }
            .card-header { background: #fff !important; color: #000 !important; border-bottom: 1px solid #000; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>View Order</h1>
        <span class="badge">Orders</span>
    </div>
    <div class="topbar-actions none">
        <button class="btn-plain" onclick="window.history.back();">Back</button>
        <button class="btn-primary" onclick="print();">Print</button>
    </div>
</div>

@if(session('status'))
<div class="alert-success">{{ session('status') }}</div>
@endif

<div class="page-body">

    {{-- Customer --}}
    <div class="card">
        <div class="card-header"><span class="dot"></span> Customer</div>
        <div class="card-body">
            @php
                $tmpCustomer = null;
                if (isset($orders) && count($orders) > 0) {
                    $tmpCustomer = $customers->firstWhere('id', $orders[0]['customerId']) ?? null;
                }
            @endphp
            <div class="customer-name">{{ $tmpCustomer->name ?? 'Unknown Customer' }}</div>
        </div>
    </div>

    {{-- Product Items --}}
    <div class="card">
        <div class="card-header"><span class="dot"></span> Product Items</div>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orderitems as $orderitem)
                        @php
                            $tmpProduct  = isset($orderitem['productId']) ? ($chemicalProducts[$orderitem['productId']] ?? 'none') : 'none';
                            $tmpUnittype = isset($orderitem['unitId']) ? ($containerSizes[$orderitem['unitId']] ?? 'none') : 'none';
                            $tmpstatus   = isset($orderitem['stateId']) ? ($statusTypes[$orderitem['stateId']] ?? 'none') : 'none';
                        @endphp
                        <tr>
                            <td>{{ $orderitem['created_at'] }}</td>
                            <td>{{ $orderitem['id'] }}</td>
                            <td>{{ $orderitem['reference'] }}</td>
                            <td>{{ $tmpProduct->name ?? 'none' }}</td>
                            <td>{{ $tmpUnittype->name ?? 'none' }}</td>
                            <td>{{ $orderitem['quantity'] ?? 'none' }}</td>
                            <td>{{ $orderitem['price'] ?? 'none' }}</td>
                            <td><span class="status-pill">{{ $tmpstatus->name ?? 'none' }}</span></td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="8">No product items on this order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>