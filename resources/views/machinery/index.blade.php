<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machinery</title>
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
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
            background: #6366f1;
            color: #fff;
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 2px 9px;
            border-radius: 99px;
        }
        .btn-new {
            background: #6366f1;
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
        .btn-new:hover { background: #4f46e5; color: #fff; }

        /* ── Alerts ── */
        .alert-bar {
            padding: 10px 24px;
            font-size: .85rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-bar.success { background: #dcfce7; border-bottom: 1px solid #bbf7d0; color: #166534; }
        .alert-bar.error   { background: #fee2e2; border-bottom: 1px solid #fca5a5; color: #991b1b; }
        .alert-bar button {
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            line-height: 1;
            color: inherit;
        }

        /* ── Page body ── */
        .page-body {
            flex: 1;
            overflow: hidden;
            padding: 16px 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-height: 0;
        }

        /* ── Table card ── */
        .table-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .table-scroll {
            flex: 1;
            overflow-y: auto;
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
            padding: 10px 16px;
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
            padding: 9px 16px;
            color: #334155;
            vertical-align: middle;
        }
        tbody td:first-child {
            color: #94a3b8;
            font-size: .75rem;
            font-weight: 600;
        }
        .machine-name {
            font-weight: 600;
            color: #0f172a;
        }
        .process-badge {
            background: #ede9fe;
            color: #5b21b6;
            font-size: .72rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 99px;
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

        /* Disabled / blurred delete button — visually muted and unclickable */
        .btn-delete:disabled {
            filter: blur(1.5px);
            opacity: .5;
            cursor: not-allowed;
            pointer-events: none;
            background: #fee2e2;
            color: #dc2626;
        }

        /* ── Pagination ── */
        .pagination-wrap {
            padding: 10px 16px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            flex-shrink: 0;
            background: #fff;
        }
        .pagination-wrap .pagination { margin: 0; }
    </style>
</head>
<body>

<script>
function closeNotification() {
    var el = document.querySelector('.alert-bar');
    if (el) el.style.display = 'none';
}
</script>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>Machinery</h1>
        <span class="badge">Fleet</span>
    </div>
    <a href="{{ route('machinery.create') }}" class="btn-new">+ New Machine</a>
</div>

{{-- ── Alerts ── --}}
@if($message = Session::get('success'))
<div class="alert-bar success">
    <span><strong>Success!</strong> {{ $message }}</span>
    <button onclick="closeNotification()">&times;</button>
</div>
@endif

@if($message = Session::get('error'))
<div class="alert-bar error">
    <span><strong>Failed!</strong> {{ $message }}</span>
    <button onclick="closeNotification()">&times;</button>
</div>
@endif

{{-- ── Page body ── --}}
<div class="page-body">
    <div class="table-card">

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Machine Name</th>
                        <th>Start Date</th>
                        <th>Position / Description</th>
                        <th>Process</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machineries as $machinery)
                    @php $tmpProcesstype = $chemicalprocesstypes[$machinery->processId] ?? null; @endphp
                    <tr>
                        <td>{{ $machinery->id }}</td>
                        <td><span class="machine-name">{{ $machinery->name }}</span></td>
                        <td>{{ $machinery->startDate }}</td>
                        <td>{{ $machinery->description }}</td>
                        <td>
                            @if($tmpProcesstype)
                                <span class="process-badge">{{ $tmpProcesstype->name }}</span>
                            @else
                                <span style="color:#94a3b8">none</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('machinery.edit', $machinery->id) }}" class="btn-edit">Edit</a>
                                <form action="{{ route('machinery.destroy', $machinery->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" disabled title="Deleting is disabled">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if($machineries->isEmpty())
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px;color:#94a3b8;font-size:.9rem;">
                            No machines found.
                            <a href="{{ route('machinery.create') }}" style="color:#6366f1;font-weight:600;">Add the first one.</a>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {!! $machineries->links() !!}
        </div>

    </div>
</div>

</body>
</html>