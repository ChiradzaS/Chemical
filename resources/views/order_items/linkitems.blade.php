<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Order Items</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('style/style.css') }}">

    <script>
        function display() {
            var check = document.getElementById("jobcardId");
            if (check == null) {
                alert('No matching items to link');
                history.back();
                event.preventDefault();
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

        .alert-success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: .9rem;
        }

        .page-body { padding: 20px 32px; width: 100%; }

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
            justify-content: space-between;
            gap: 7px;
        }
        .card-header-left { display: flex; align-items: center; gap: 7px; }
        .card-header .dot { width: 6px; height: 6px; background: #0ea5e9; border-radius: 50%; flex-shrink: 0; }

        .btn-link {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 5px 16px;
            font-size: .75rem;
            font-weight: 700;
            cursor: pointer;
            text-transform: none;
            letter-spacing: normal;
        }
        .btn-link:hover { background: #0284c7; }

        /* ── Table ── */
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

        input[type="checkbox"] { width: 15px; height: 15px; }

        .pagination-wrap { margin-top: 16px; }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>Select Items to Link to Jobcard</h1>
        <span class="badge">Orders</span>
    </div>
    <button class="btn-plain" onclick="javascript:window.history.back();">Go Back</button>
</div>

<div class="page-body">

@if ($message = Session::get('success'))
<div class="alert-success" style="margin-bottom:16px;">{{ $message }}</div>
@endif

<form action="{{ route('order_items.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="card">
    <div class="card-header">
        <div class="card-header-left"><span class="dot"></span> Order Items</div>
        <button type="submit" onclick="display()" class="btn-link">Link Order Items</button>
    </div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Id</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Product Type</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Unit Type</th>
                <th>Total Cost</th>
                <th>Due Date</th>
                <th width="80px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order_items as $order_item)
            @php $tmpProduct = $porducts[$order_item->productId]; @endphp
            @php $tmpUnittype = $unittypes[$order_item->unitId]; @endphp
            @php $tmpCustomer = $customers[$order_item->customerId]; @endphp

            <tr>
                <td>{{ $order_item->id }}</td>
                <td>{{ $order_item->created_at }}</td>
                <td>{{ $tmpCustomer->name }}</td>
                <td>{{ $tmpProduct->name }}</td>
                <td>{{ $order_item->quantity }}</td>
                <td>{{ $order_item->price }}</td>
                <td>{{ $tmpUnittype->name }}</td>
                <td>{{ $order_item->totalPrice }}</td>
                <td>{{ $order_item->dueDate }}</td>
                <td>
                    <input type="checkbox" id='checkbox' name="item_ids[]" value="{{$order_item->id}}">
                    <input type="hidden" id='jobcardId' name="jobcardId" value="{{$jobcardId}}">
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination-wrap">
    {!! $order_items->links() !!}
</div>

</form>

</div>{{-- end page-body --}}

</body>
</html>