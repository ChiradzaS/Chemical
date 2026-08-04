<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All orders list</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script type='text/javascript'>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        function checked(ordersId) {

            const checkboxes = document.querySelectorAll(`input[type='checkbox'][id='${ordersId}']`);

            const selectedValues = [];

            checkboxes.forEach((checkbox, index) => {
                if (checkbox.checked) {
                    selectedValues.push(checkbox.value);
                }
            });

            const jsonData = encodeURIComponent(JSON.stringify(selectedValues));

            $.ajax({
                url: "{{ route('collect') }}",
                type: "POST",
                data: {
                    _token: CSRF_TOKEN,
                    jsonData: jsonData,
                },
                dataType: "json",
                success: function (response) {

                    queryString = 'data=' + jsonData;

                    const createUrl = 'deliveries/show';

                    document.getElementById('createButton').setAttribute('href', createUrl + '?' + queryString);
                    window.location.href = createUrl + '?' + queryString;

                },
                error: function (xhr) {
                    alert('failed');
                },
            });

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

        .page-body { padding: 20px 32px; width: 100%; }

        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
        }

        /* ── Table ── */
        table.orders-list { width: 100%; border-collapse: collapse; }
        table.orders-list > thead > tr > th {
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

        tr.group-row td {
            background: #0f172a;
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            padding: 8px 10px;
            border-top: 1px solid #1e293b;
        }
        tr.group-row .group-id { color: #94a3b8; font-weight: 800; }
        tr.group-row .group-customer { font-size: .88rem; }

        tr.item-row td {
            padding: 8px 10px;
            font-size: .82rem;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        tr.item-row:hover td { background: #f0f9ff; }

        tr.action-row td {
            padding: 8px 10px 16px;
            background: #fff;
            border-bottom: 8px solid #f1f5f9;
            text-align: right;
        }

        .status-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: .7rem;
            font-weight: 700;
            background: #e0f2fe;
            color: #075985;
        }
        .status-pill.complete { background: #dcfce7; color: #166534; }

        input.larger { width: 15px; height: 15px; }

        .btn-invoice {
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 20px;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-invoice:hover { background: #1e293b; }

        #createButton { display: none; }

        span.select2.select2-container.select2-container--classic { width: 70% !important; }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<div class="topbar">
    <h1>All Orders List</h1>
    <span class="badge">Orders</span>
</div>

<button type="hidden" id="createButton">Create</button>

<div class="page-body">
<div class="card">
<table class="orders-list">
<thead>
<tr>
<th>Id</th>
<th>Customer</th>
<th>Reference</th>
<th></th>
<th>Status</th>
<th></th>
<th>Product</th>
<th>Quantity</th>
<th>Outstanding</th>
<th>Pack</th>
<th>Price</th>
<th>Action</th>
</tr>
</thead>
<tbody>

@foreach ($orders as $order)
@php $tmpCustomer = $customers[$order->customerId]; @endphp
@php  $orderitems = App\Models\Order_item::where('ordersId',$order->id )->get(); @endphp
@php $tmpstatus = $statustypes[$order->stateId]; @endphp

<tr class="group-row">
<td><span class="group-id">{{ $order->id }}</span></td>
<td colspan="2"><span class="group-customer">{{ $tmpCustomer->name }}</span></td>
<td></td>
<td>{{ $tmpstatus->name }}</td>
<td></td>
<td colspan="6"></td>
</tr>

@foreach ($orderitems as $orderitem)
@php $tmpProduct = $chemicalProducts[$orderitem->productId]; @endphp
@php $tmpUnittype = $unittypes[$orderitem->unitId]; @endphp
@php $tmpstatus = $statustypes[$orderitem->stateId]; @endphp

<tr class="item-row">
<td>
    <input type="hidden" id="orderitemId-{{ $orderitem->ordersId}}" name="orderitemId" value="{{ $orderitem->id}}">
</td>
<td class="{{ $orderitem->customerId}}"></td>
<td><strong>{{$orderitem->reference}}</strong></td>
<td></td>
<td>
    @if($tmpstatus->name === "Complete")
        <span class="status-pill complete">{{$tmpstatus->name}} {{ $orderitem->DateComplete }}</span>
    @else
        <span class="status-pill">{{$tmpstatus->name}}</span>
    @endif
</td>
<td></td>
<td>{{$tmpProduct->name}}</td>
<td>{{$orderitem->quantity}}</td>
<td>{{$orderitem->outstanding}}</td>
<td>{{$tmpUnittype->name}}</td>
<td>{{$orderitem->price}}</td>
<td>
    <input type="checkbox" class="larger" id="{{ $orderitem->ordersId}}" name="orderitemId" value="{{ $orderitem->id}}">
</td>
</tr>
@endforeach

<tr class="action-row">
<td colspan="12">
<button type="button" class="btn-invoice" onclick="checked({{ $orderitem->ordersId}})">Invoice</button>
</td>
</tr>

@endforeach

</tbody>
</table>
</div>
</div>

</body>
</html>