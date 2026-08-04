<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>
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
            padding: 16px 24px;
            display: flex;
            flex-direction: column;
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
            font-size: .85rem;
        }
        thead tr {
            background: #0f172a;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        thead th {
            padding: 11px 18px;
            font-size: .66rem;
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
            padding: 10px 18px;
            color: #334155;
            vertical-align: middle;
        }
        tbody td:first-child {
            color: #94a3b8;
            font-size: .75rem;
            font-weight: 600;
        }
        .user-name {
            font-weight: 600;
            color: #0f172a;
        }
        .user-email {
            color: #64748b;
        }

        /* ── Row actions ── */
        .row-actions { display: flex; gap: 6px; align-items: center; }
        .btn-edit {
            background: #e0f2fe;
            color: #0369a1;
            border: none;
            border-radius: 6px;
            padding: 5px 14px;
            font-size: .76rem;
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
            padding: 5px 14px;
            font-size: .76rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
        }
        .btn-delete:hover { background: #fecaca; }
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
        <h1>Users</h1>
        <span class="badge">Admin</span>
    </div>
    <a href="{{ route('users.create') }}" class="btn-new">+ New User</a>
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td><span class="user-name">{{ $user->name }}</span></td>
                        <td><span class="user-email">{{ $user->email }}</span></td>
                        <td>{{ $user->created_at }}</td>
                        <td>
                            <div class="row-actions">
                                <!-- <a href="{{ route('users.edit', $user->id) }}" class="btn-edit">Edit</a> -->
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete"
                                        onclick="return confirm('Delete {{ addslashes($user->name) }}?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if($users->isEmpty())
                    <tr>
                        <td colspan="5" style="text-align:center;padding:48px;color:#94a3b8;font-size:.9rem;">
                            No users found.
                            <a href="{{ route('users.create') }}" style="color:#0ea5e9;font-weight:600;">Create the first one.</a>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>