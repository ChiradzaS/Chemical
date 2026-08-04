<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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
            background: #f59e0b;
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
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,.12);
        }

        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: .78rem;
        }

        .btn-row {
            display: flex;
            gap: 10px;
            margin-top: 6px;
        }

        .btn-back {
            flex: 1;
            background: #f1f5f9;
            color: #475569;
            border: 1.5px solid #cbd5e1;
            border-radius: 9px;
            padding: 11px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background .15s;
        }
        .btn-back:hover { background: #e2e8f0; }

        .btn-submit {
            flex: 2;
            background: #f59e0b;
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 11px;
            font-size: .9rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-submit:hover { background: #d97706; }
    </style>
</head>
<body>

<div class="card">

    <div class="card-header">
        <div class="icon">✎</div>
        <div>
            <h1>Edit User</h1>
            <p>{{ $user->name }}</p>
        </div>
    </div>

    @if(session('status'))
    <div class="alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">

            <div class="field">
                <label>User Name</label>
                <input type="text" name="name" value="{{ $user->name }}">
                @error('name')<div class="alert-danger">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="{{ $user->email }}">
                @error('email')<div class="alert-danger">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">
                @error('password')<div class="alert-danger">{{ $message }}</div>@enderror
            </div>

            <div class="btn-row">
                <a href="javascript:history.back()" class="btn-back">← Back</a>
                <button type="submit" class="btn-submit">Update User</button>
            </div>

        </div>
    </form>

</div>

</body>
</html>