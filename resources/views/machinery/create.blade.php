<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Machinery</title>
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
        .topbar-actions { display: flex; gap: 10px; }
        .btn-cancel {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 6px 18px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: color .15s, border-color .15s;
        }
        .btn-cancel:hover { color: #fff; border-color: #64748b; }
        .btn-submit {
            background: #6366f1;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 22px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-submit:hover { background: #4f46e5; }

        .alert-success {
            background: #dcfce7;
            border-bottom: 1px solid #bbf7d0;
            color: #166534;
            padding: 10px 24px;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .form-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px 24px 32px;
            min-height: 0;
        }

        .page-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            align-items: start;
        }

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
            gap: 7px;
        }
        .card-header .dot {
            width: 6px; height: 6px;
            background: #6366f1;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .card-body { padding: 14px 16px; }

        .grid { display: grid; gap: 10px; }
        .g2 { grid-template-columns: 1fr 1fr; }
        .span2 { grid-column: span 2; }

        .field { display: flex; flex-direction: column; gap: 3px; }
        .field label {
            font-size: .65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .field label .req { color: #ef4444; margin-left: 2px; }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        textarea,
        select {
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            padding: 5px 10px;
            font-size: .82rem;
            color: #1e293b;
            background: #fff;
            width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        }
        input:invalid, textarea:invalid, select:invalid {
            border-color: #fca5a5;
        }
        textarea { resize: vertical; min-height: 72px; }

        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            border-radius: 5px;
            padding: 4px 8px;
            font-size: .75rem;
            margin-top: 3px;
        }

        /* Select2 tweaks */
        .select2-container--classic .select2-selection--single {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 7px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            font-size: .82rem !important;
        }
        .select2-container { width: 100% !important; }
    </style>
</head>
<body>

<form action="{{ route('machinery.store') }}" method="POST" enctype="multipart/form-data" style="display:contents">
@csrf

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({ theme: "classic" });
});
</script>

{{-- ── Top bar ── --}}
<div class="topbar">
    <div class="topbar-left">
        <h1>Add Machinery</h1>
        <span class="badge">New</span>
    </div>
    <div class="topbar-actions">
        <a href="{{ url()->previous() }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="btn-submit">Save Machine</button>
    </div>
</div>

@if(session('status'))
<div class="alert-success">{{ session('status') }}</div>
@endif

<div class="form-body">
<div class="page-grid">

    {{-- ══ LEFT ══ --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Machine Identity --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Machine Identity</div>
            <div class="card-body">
                <div class="grid g2">
                    <div class="field span2">
                        <label>Machine Name <span class="req">*</span></label>
                        <input type="text" name="name" required>
                        @error('name')<div class="alert-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label>Machine Type <span class="req">*</span></label>
                        <select name="machineryTypeId" class="js-example-basic-single" required>
                            <option value="" disabled selected hidden>-- Select type --</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Process <span class="req">*</span></label>
                        <select name="processId" class="js-example-basic-single" required>
                            <option value="" disabled selected hidden>-- Select process --</option>
                            @foreach($chemicalprocesstypes as $processtype)
                                <option value="{{ $processtype->id }}">{{ $processtype->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Ref No <span class="req">*</span></label>
                        <input type="text" name="RefNo" required>
                    </div>
                    <div class="field">
                        <label>Serial No <span class="req">*</span></label>
                        <input type="text" name="serialNo" required>
                    </div>
                    <div class="field span2">
                        <label>Address of Machine <span class="req">*</span></label>
                        <input type="text" name="addressOfMachine" required>
                    </div>
                    <div class="field span2">
                        <label>Description <span class="req">*</span></label>
                        <input type="text" name="description" required>
                    </div>
                    <div class="field span2">
                        <label>Other Notes <span class="req">*</span></label>
                        <textarea name="other" required></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Valuation & Dates --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Valuation &amp; Dates</div>
            <div class="card-body">
                <div class="grid g2">
                    <div class="field">
                        <label>Book Value <span class="req">*</span></label>
                        <input type="text" name="bookValue" required>
                    </div>
                    <div class="field">
                        <label>Realistic Value <span class="req">*</span></label>
                        <input type="text" name="realisticValue" required>
                    </div>
                    <div class="field">
                        <label>Start Date <span class="req">*</span></label>
                        <input type="date" name="startDate" required>
                    </div>
                    <div class="field">
                        <label>End Date <span class="req">*</span></label>
                        <input type="date" name="endDate" required>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end left --}}

    {{-- ══ RIGHT ══ --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Manufacturer --}}
        <div class="card">
            <div class="card-header"><span class="dot"></span> Manufacturer</div>
            <div class="card-body">
                <div class="grid g2">
                    <div class="field span2">
                        <label>Manufacturer Name <span class="req">*</span></label>
                        <input type="text" name="manufactureOfMachine" required>
                    </div>
                    <div class="field">
                        <label>Email <span class="req">*</span></label>
                        <input type="text" name="emailAddressManufacturer" required>
                    </div>
                    <div class="field">
                        <label>Website <span class="req">*</span></label>
                        <input type="text" name="websiteManufacturer" required>
                    </div>
                    <div class="field">
                        <label>Contact Person <span class="req">*</span></label>
                        <input type="text" name="contactPersonOfManufacture" required>
                    </div>
                    <div class="field">
                        <label>Contact Details <span class="req">*</span></label>
                        <input type="text" name="contactDetailsOfManufacture" required>
                    </div>
                    <div class="field span2">
                        <label>Address <span class="req">*</span></label>
                        <input type="text" name="addressOfManufacturer" required>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end right --}}

</div>{{-- end page-grid --}}
</div>{{-- end form-body --}}

</form>
</body>
</html>