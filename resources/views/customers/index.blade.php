<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
        }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            overflow: hidden;
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
            flex-shrink: 0;
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
        .btn-new {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 20px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-new:hover { background: #0284c7; color: #fff; }

        /* ── Alert ── */
        .alert-success {
            background: #dcfce7;
            border-bottom: 1px solid #bbf7d0;
            color: #166534;
            padding: 10px 24px;
            font-size: .85rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success button {
            background: none;
            border: none;
            color: #166534;
            font-size: 1.1rem;
            cursor: pointer;
        }

        /* ── Page body — fixed height, no scroll ── */
        .page-body {
            flex: 1;
            overflow: hidden;
            padding: 16px 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-height: 0;
        }

        /* ── Filter card — fixed height ── */
        .filter-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            padding: 14px 20px;
            display: flex;
            align-items: flex-end;
            gap: 16px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }
        .filter-field { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 180px; }
        .filter-field label {
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        select, input[type="text"] {
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            padding: 6px 10px;
            font-size: .82rem;
            color: #1e293b;
            background: #fff;
            width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }
        select:focus, input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,.12);
        }
        .btn-search {
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 7px 24px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s;
            height: 34px;
            flex-shrink: 0;
        }
        .btn-search:hover { background: #1e293b; }

        /* ── Table card — fills remaining space, scrolls internally ── */
        .table-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;  /* critical — allows flex child to shrink */
        }

        /* ── Scrollable table area ── */
        .table-scroll {
            flex: 1;
            overflow: auto;
            min-height: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .83rem;
        }
        thead tr {
            background: #0f172a;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        thead th {
            padding: 10px 14px;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .1em;
            white-space: nowrap;
            text-align: left;
        }
        tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .1s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }
        tbody td {
            padding: 9px 14px;
            color: #334155;
            vertical-align: middle;
        }
        tbody td:first-child {
            color: #94a3b8;
            font-size: .75rem;
            font-weight: 600;
        }
        .customer-name {
            font-weight: 600;
            color: #0f172a;
        }
        .sub {
            display: block;
            font-size: .7rem;
            color: #94a3b8;
            margin-top: 1px;
        }
        .mono { font-family: ui-monospace, 'Consolas', monospace; font-size: .78rem; }

        /* A required field that was never filled in */
        .missing {
            color: #dc2626;
            font-size: .7rem;
            font-weight: 700;
            background: #fee2e2;
            border-radius: 4px;
            padding: 1px 6px;
            white-space: nowrap;
        }

        /* ── Row actions ── */
        .row-actions { display: flex; gap: 6px; align-items: center; }
        .btn-edit {
            background: #e0f2fe;
            color: #0369a1;
            border: none;
            border-radius: 6px;
            padding: 4px 14px;
            font-size: .75rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
            white-space: nowrap;
        }
        .btn-edit:hover { background: #bae6fd; color: #0369a1; }
        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 6px;
            padding: 4px 14px;
            font-size: .75rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }
        .btn-delete:hover { background: #fecaca; }

        /* ── Pagination — fixed at card bottom ── */
        .pagination-wrap {
            padding: 10px 16px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            flex-shrink: 0;
            background: #fff;
        }
        .pagination-wrap .pagination { margin: 0; }

        /* Select2 tweaks */
        .select2-container--classic .select2-selection--single {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 7px !important;
            height: 34px !important;
            display: flex !important;
            align-items: center !important;
            font-size: .82rem !important;
        }
        .select2-container { width: 100% !important; }
    </style>
</head>
<body>

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({ theme: "classic" });
});
function closeNotification() {
    var el = document.querySelector('.alert-success');
    if (el) el.style.display = 'none';
}
</script>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>Customers</h1>
        <span class="badge">CRM</span>
    </div>
    <a href="{{ route('customers.create') }}" class="btn-new">+ New Customer</a>
</div>

{{-- ── Alert ── --}}
@if($message = Session::get('success'))
<div class="alert-success">
    <span><strong>Success!</strong> {{ $message }}</span>
    <button onclick="closeNotification()">&times;</button>
</div>
@endif

{{-- ── Page body ── --}}
<div class="page-body">

    {{-- Filter bar --}}
    <form action="{{ route('customers.index') }}" method="GET">
        <div class="filter-card">
            <div class="filter-field">
                <label>Customer Type</label>
                <select name="customerType" class="js-example-basic-single">
                    <option value="" disabled selected hidden>All types</option>
                    @foreach($customertypes as $ct)
                        <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label>Customer Name</label>
                <select name="customerId" class="js-example-basic-single">
                    <option value="" disabled selected hidden>All customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" name="action" value="query" class="btn-search">Search</button>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-card">

        {{-- Scrollable area --}}
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>VAT Number</th>
                        <th>Address</th>
                        <th>City / Region</th>
                        <th>Postal Code</th>
                        <th>Country</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    @php
                        // The six fields the create form now requires — anything blank
                        // here is a legacy record that predates the validation rules.
                        $cityRegion = collect([$customer->pOCity, $customer->pORegion])
                            ->filter(fn($v) => trim((string) $v) !== '')
                            ->implode(', ');
                    @endphp
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>
                            <span class="customer-name">{{ $customer->name }}</span>
                            @if(trim((string) $customer->accountNumber) !== '')
                                <span class="sub">Acc {{ $customer->accountNumber }}</span>
                            @endif
                        </td>
                        <td class="mono">
                            @if(trim((string) $customer->vatNo) !== '')
                                {{ $customer->vatNo }}
                            @else
                                <span class="missing">Missing</span>
                            @endif
                        </td>
                        <td>
                            @if(trim((string) $customer->pOAddressLine1) !== '')
                                {{ $customer->pOAddressLine1 }}
                                @if(trim((string) $customer->pOAddressLine2) !== '')
                                    <span class="sub">{{ $customer->pOAddressLine2 }}</span>
                                @endif
                            @else
                                <span class="missing">Missing</span>
                            @endif
                        </td>
                        <td>
                            @if($cityRegion !== '')
                                {{ $cityRegion }}
                            @else
                                <span class="missing">Missing</span>
                            @endif
                        </td>
                        <td class="mono">
                            @if(trim((string) $customer->pOPostalCode) !== '')
                                {{ $customer->pOPostalCode }}
                            @else
                                <span class="missing">Missing</span>
                            @endif
                        </td>
                        <td>
                            @if(trim((string) $customer->pOCountry) !== '')
                                {{ $customer->pOCountry }}
                            @else
                                <span class="missing">Missing</span>
                            @endif
                        </td>
                        <td>{{ $customer->emailAddress }}</td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn-edit">Edit</a>
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete"
                                        onclick="return confirm({!! json_encode('Delete ' . $customer->name . '?') !!})">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if($customers->isEmpty())
                    <tr>
                        <td colspan="9" style="text-align:center;padding:48px;color:#94a3b8;font-size:.9rem;">
                            No customers found.
                            <a href="{{ route('customers.create') }}" style="color:#0ea5e9;font-weight:600;">Create the first one.</a>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>{{-- end table-scroll --}}

        {{-- Pagination pinned at bottom --}}
        <div class="pagination-wrap">
            {!! $customers->links() !!}
        </div>

    </div>{{-- end table-card --}}

</div>{{-- end page-body --}}

</body>
</html>