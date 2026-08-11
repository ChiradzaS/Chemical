<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Type</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Select2 (if needed elsewhere) -->
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

    <style>
        body {
            background: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }

        .form-label {
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="container py-5">

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Edit Type</h3>
        </div>

        <div class="card-body">

            <form action="{{ route('types.update', $type->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <!-- Type Name -->
                    <div class="col-md-6">
                        <label class="form-label">Type Name</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $type->name) }}"
                               class="form-control">

                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Group Type -->
                    <div class="col-md-6">
                        <label class="form-label">Group Type</label>

                        <input type="text"
                               value="{{ $type->groupType }}"
                               class="form-control bg-light"
                               readonly>

                        <input type="hidden"
                               name="groupType"
                               value="{{ $type->groupType }}">
                    </div>

                    <!-- Description -->
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text"
                               name="description"
                               value="{{ old('description', $type->description) }}"
                               class="form-control">

                        @error('description')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Value.
                         type="text" on purpose: a number input rejects decimals
                         unless a step is set, and rejects the plain text that
                         other group types keep in this same column. -->
                    <div class="col-md-6">
                        <label class="form-label">Value</label>
                        <input type="text"
                               name="value"
                               id="valueField"
                               inputmode="decimal"
                               autocomplete="off"
                               value="{{ old('value', $type->value) }}"
                               class="form-control">

                        <div class="form-text" id="valueHint">
                            Decimals allowed — 0.500 means 500 ml
                        </div>

                        @error('value')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Level -->
                    <div class="col-md-6">
                        <label class="form-label">Level</label>
                        <input type="number"
                               step="1"
                               name="level"
                               value="{{ old('level', $type->level) }}"
                               class="form-control">

                        @error('level')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Parent Key -->
                    <div class="col-md-6">
                        <label class="form-label">Parent Key</label>
                        <input type="number"
                               step="1"
                               name="parentKey"
                               value="{{ old('parentKey', $type->parentKey) }}"
                               class="form-control">

                        @error('parentKey')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Top Value -->
                    <div class="col-md-6">
                        <label class="form-label">Top Value</label>
                        <input type="number"
                               step="any"
                               name="topValue"
                               value="{{ old('topValue', $type->topValue) }}"
                               class="form-control">

                        @error('topValue')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Child Type -->
                    <div class="col-md-6">
                        <label class="form-label">Child Type</label>
                        <input type="text"
                               name="childType"
                               value="{{ old('childType', $type->childType) }}"
                               class="form-control">

                        @error('childType')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Label -->
                    <div class="col-md-6">
                        <label class="form-label">Label</label>
                        <input type="text"
                               name="label"
                               value="{{ old('label', $type->label) }}"
                               class="form-control">

                        @error('label')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Start Time -->
                    <div class="col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="time"
                               name="start_time"
                               value="{{ $type->start_time }}"
                               class="form-control">

                        @error('start_time')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- End Time -->
                    <div class="col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="time"
                               name="end_time"
                               value="{{ $type->end_time }}"
                               class="form-control">

                        @error('end_time')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <a href="{{ route('types.index') }}"
                       class="btn btn-outline-secondary">
                        Back
                    </a>

                    <button type="submit"
                            class="btn btn-primary px-4">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

<script>
    // A comma decimal ("0,500") is a common typo on a local keyboard and parses
    // as NaN downstream, so it is corrected as soon as the field is left.
    (function () {
        var field = document.getElementById('valueField');
        var hint  = document.getElementById('valueHint');
        if (!field || !hint) return;

        var defaultHint = hint.textContent;

        field.addEventListener('blur', function () {
            var raw = field.value.trim();

            if (raw === '') {
                hint.classList.remove('text-danger');
                hint.textContent = defaultHint;
                return;
            }

            var fixed = raw.replace(',', '.');

            if (/^\d*\.?\d+$/.test(fixed)) {
                field.value = fixed;
                hint.classList.remove('text-danger');
                hint.textContent = defaultHint;
            } else {
                // not blocked — some group types legitimately store text here
                hint.classList.add('text-danger');
                hint.textContent = 'Not a number. Fine for text values, but container sizes must be numeric.';
            }
        });
    })();
</script>

</body>
</html>