<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>
<!--------------------------------------------------------------------------------------------------------------------------->

<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script src="{{ asset('public/js/script.js') }}" ></script>
<body style="background-color: #f4f6f9;">
<div class="container-fluid mt-3">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">

<br>
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success! </strong><strong>{{ $message }}</strong>
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

@media print {
    .no-print {
        display: none !important;
    }
}

.alert.alert-success.alert-dismissible {
    padding: 31px;
    font-size: 18px;
}

.alert.alert-danger.alert-dismissible {
    padding: 31px;
    font-size: 18px;
}

/* CSS */
.button-33 {
  background-color:rgb(60, 240, 126);
  border-radius: 100px;
  box-shadow: rgba(44, 187, 99, .2) 0 -25px 18px -14px inset,rgba(44, 187, 99, .15) 0 1px 2px,rgba(44, 187, 99, .15) 0 2px 4px,rgba(44, 187, 99, .15) 0 4px 8px,rgba(44, 187, 99, .15) 0 8px 16px,rgba(44, 187, 99, .15) 0 16px 32px;
  color: green;
  cursor: pointer;
  display: inline-block;
  font-family: CerebriSans-Regular,-apple-system,system-ui,Roboto,sans-serif;
  padding: 9px 20px;
  text-align: center;
  text-decoration: none;
  transition: all 250ms;
  border: 0;
  font-size: 20px;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}

.button-33:hover {
  box-shadow: rgba(44,187,99,.35) 0 -25px 18px -14px inset,rgba(44,187,99,.25) 0 1px 2px,rgba(44,187,99,.25) 0 2px 4px,rgba(44,187,99,.25) 0 4px 8px,rgba(44,187,99,.25) 0 8px 16px,rgba(44,187,99,.25) 0 16px 32px;
  transform: scale(1.05) rotate(-1deg);
}

/* ── Production cards ── */
.prod-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    margin-bottom: 26px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    transition: box-shadow 200ms ease;
}

.prod-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.09);
}

.prod-card-header {
    background: linear-gradient(135deg, #334155, #1e293b);
    color: #fff;
    padding: 18px 24px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.prod-card-header .prod-title {
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: 0.3px;
}

.prod-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    font-size: 13px;
    color: #cbd5e1;
}

.prod-meta strong {
    color: #fff;
    font-weight: 600;
}

.prod-card-body {
    padding: 0;
}

.prod-items-table {
    width: 100%;
    margin-bottom: 0;
    font-size: 14px;
}

.prod-items-table thead th {
    background-color: #f8f9fa;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    padding: 12px 20px;
}

.prod-items-table thead th.qty-col {
    width: 140px;
}

.prod-items-table tbody td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
}

.prod-items-table tbody tr:last-child td {
    border-bottom: none;
}

.prod-items-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Big, bold quantity — first column, immediately draws the eye */
.qty-cell {
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.qty-value {
    font-size: 28px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}

.qty-unit {
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: lowercase;
}

.product-name {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.package-badge {
    display: inline-block;
    background: #eef2ff;
    color: #4338ca;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 100px;
}

.detail-link {
    color: #1d4ed8;
    font-weight: 600;
    text-decoration: none;
    font-size: 13px;
    white-space: nowrap;
}

.detail-link:hover {
    text-decoration: underline;
}

.filter-bar {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    margin-bottom: 20px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 16px;
}

.filter-row .filter-field {
    min-width: 180px;
}

</style>

<div class="filter-bar no-print">
<form action="{{ route('allocateproductions.index', ['prntReport' => 'PRODUCTION_LIST'])}}" method="GET" >
<div class="filter-row">

    <div class="filter-field">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">Date</span>
            </div>
            <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="form-control">
        </div>
    </div>

    <div class="filter-field">
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon2">To</span>
            </div>
            <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="form-control">
        </div>
    </div>

    <div class="filter-field">
        <select name="operatorId" id="operatorId" style="width:200px;" class="form-control" placeholder="-- Select Process --">
            <option value="" {{ $operatorId == '' ? 'selected' : '' }}>--All Operators--</option>
            @foreach($operators as $operator)
                <option value="{{ $operator->id }}" @if($operatorId == $operator->id) selected @endif>
                    {{ $operator->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-field">
        <select name="machineryId" id="machineryId" style="width:200px;" class="form-control" placeholder="-- Select Process --">
            <option value="" {{ $machineryId == '' ? 'selected' : '' }}>--All Machines--</option>
            @foreach($machinetypes as $machinetype)
                <option value="{{$machinetype->id}}" @if($machineryId ==$machinetype->id) selected @endif>
                    {{$machinetype->name  }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-field">
        <select name="shiftId" id="shiftId" style="width:200px;" class="form-control" placeholder="-- Select Process --">
            <option value="" {{ $shiftId == '' ? 'selected' : '' }}>--All Shifts--</option>
            @foreach($shifttypes as $shifttype)
                <option value="{{  $shifttype->id }}" @if($shiftId == $shifttype->id) selected @endif>
                    {{ $shifttype->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="filter-field">
        <button type="submit" value="query" name="action" class="button-33">Search</button>
    </div>

</div>
</form>
</div>


@if(isset($productions) && !empty($productions))
    @php
        // Group flat item rows by production
        $groupedByProduction = collect($productions)->groupBy('item_productionId');
    @endphp

    @foreach($groupedByProduction as $productionId => $items)
        @php
            $first = $items->first();

            $tmpuser        = $user[ $first['item_employeeId'] ] ?? null;
            $tmpShifttype   = $shifttypes[ $first['item_shiftId'] ] ?? null;
            $tmpmachine     = $machinetypes[ $first['item_machineId'] ] ?? null;

            $totalQuantity  = $items->sum('item_qnt');

            // Sum items with the same product together
            $itemsByProduct = $items->groupBy('item_productId');
        @endphp

        <div class="prod-card">

            <div class="prod-card-header">
                <div class="prod-title">
                    <span>Production #{{ $productionId }}</span>
                </div>

                <div class="prod-meta">
                    <span>Employee: <strong>{{ $tmpuser->name ?? '—' }}</strong></span>
                    <span>Shift: <strong>{{ $tmpShifttype->name ?? '—' }}</strong></span>
                    <span>Machine: <strong>{{ $tmpmachine->name ?? '—' }}</strong></span>
                    <span>Date: <strong>{{ $first['item_created_at'] ?? '—' }}</strong></span>
                    <span>Total Qty: <strong>{{ $totalQuantity }}</strong></span>
                </div>
            </div>

            <div class="prod-card-body">
                <table class="prod-items-table">
                    <thead>
                        <tr>
                            <th class="qty-col">Quantity</th>
                            <th>Product</th>
                            <th>Package</th>
                            <th>Detailed list</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsByProduct as $productId => $productItems)
                            @php
                                $itemFirst      = $productItems->first();
                                $tmpProcesstype = $processtypes[ $itemFirst['item_processId'] ] ?? null;
                                $tmpProduct     = $chemicalProducts[ $productId ] ?? null;
                                $tmpPackage     = $containerSizes[ $itemFirst['item_unitId'] ] ?? null;
                                $productQty     = $productItems->sum('item_qnt');
                            @endphp
                            <tr>
                                <td>
                                    <div class="qty-cell">
                                        <span class="qty-value">{{ $productQty }}</span>
                                        @if(($tmpProcesstype->name ?? null) == 'Extruding')
                                            <span class="qty-unit">kgs</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="product-name">{{ $tmpProduct->name ?? '—' }}</span></td>
                                <td><span class="package-badge">{{ $tmpPackage->name ?? '—' }}</span></td>
                                <td>
                                    <a class="detail-link" href="{{ route('allocateproductions.create',
                                        [
                                            'id'             => $itemFirst['item_jobcarditemId'] ?? 0,
                                            'created_at'     => $itemFirst['item_created_at'],
                                            'user_id'        => $itemFirst['item_employeeId'],
                                            'shift_id'       => $itemFirst['item_shiftId'],
                                            'production_id'  => $itemFirst['item_productionId'],
                                            'product'        => $productId
                                        ]) }}">
                                        Detailed Production List
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @endforeach
@else
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" onclick="closeNotification()">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>No production data available!</strong> {{ $message ?? '' }}
    </div>
@endif

</div>
</div>
</div>
</div>
</body>
</html>