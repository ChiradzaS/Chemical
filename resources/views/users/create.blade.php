<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0,0,0,.06);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .card-header {
            background: #0f172a;
            color: #fff;
            padding: 18px 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header .icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #0ea5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .card-header h1 {
            font-size: 1.05rem;
            font-weight: 700;
        }
        .card-header p {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 1px;
        }

        .alert-success {
            background: #dcfce7;
            border-bottom: 1px solid #bbf7d0;
            color: #166534;
            padding: 12px 28px;
            font-size: .85rem;
        }

        .card-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .field { display: flex; flex-direction: column; gap: 5px; }
        .field label {
            font-size: .72rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .field input {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: .9rem;
            color: #1e293b;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }
        .field input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,.12);
        }

        .field small.hint {
            font-size: .68rem;
            color: #94a3b8;
        }

        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: .78rem;
        }

        .btn-submit {
            background: #0ea5e9;
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 11px;
            font-size: .92rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
            margin-top: 6px;
        }
        .btn-submit:hover { background: #0284c7; }
    </style>
</head>
<body>

<div class="card">

    <div class="card-header">
        <div class="icon">👤</div>
        <div>
            <h1>Add User</h1>
            <p>Create a new system user account</p>
        </div>
    </div>

    @if(session('status'))
    <div class="alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <div class="card-body">

            <div class="field">
                <label>User Name</label>
                <input type="text" name="name" value="" autocomplete="off">
                @error('name')<div class="alert-danger">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="" autocomplete="off">
                @error('email')<div class="alert-danger">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label>Password (4-digit PIN)</label>
                <input
                    type="password"
                    name="password"
                    value=""
                    autocomplete="off"
                    inputmode="numeric"
                    pattern="\d{4}"
                    maxlength="4"
                    minlength="4"
                    title="Password must be exactly 4 digits">
                <small class="hint">Must be exactly 4 digits (0-9)</small>
                @error('password')<div class="alert-danger">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn-submit">Create User</button>

        </div>
    </form>

</div>

<script>
    // Restrict password field to numeric input only, max 4 digits
    document.querySelector('input[name="password"]').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });
</script>

</body>
</html>