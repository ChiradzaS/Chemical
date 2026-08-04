<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add New Type</title>

<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">

<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>

<style>
body {
    background: #f4f6f9;
}

.card {
    border-radius: 12px;
}

hr {
    height: 4px;
    background: #00A4BD;
    border: none;
}

.spinner-container {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
}

.spinner {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 4px solid #ccc;
    border-top-color: #007bff;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

</head>

<body>

<div class="container py-4">

<div class="card shadow p-4">

    <h3 class="text-center mb-4">Add New Type</h3>

    <form action="{{ route('types.store') }}" method="POST">
        @csrf

        <!-- CLONE -->
        <div class="mb-3">
            <label class="form-label fw-bold">Clone Type</label>

            <select name="groupname" id="groupname" class="form-control" onchange="cloneType()">
                <option disabled selected>---- select product to clone ----</option>
                @foreach($grouptypes as $grouptype)
                    <option value="{{ $grouptype }}">{{ $grouptype }}</option>
                @endforeach
            </select>
        </div>

        <hr>

        <div class="row g-3">

            <div class="col-md-6">
                <label>Type Name</label>
                <input type="text"
                       name="name"
                       id="name"
                       class="form-control"
                       value="{{ old('name') }}">
            </div>

            <div class="col-md-6">
                <label>Description</label>
                <input type="text"
                       name="description"
                       id="description"
                       class="form-control"
                       value="{{ old('description') }}">
            </div>

            <div class="col-md-6">
                <label>Group Type</label>
                <input type="text"
                       name="groupType"
                       id="groupType"
                       class="form-control"
                       value="{{ old('groupType') }}"
                       readonly>
            </div>

            <div class="col-md-6">
                <label>Value</label>
                <input type="number"
                       name="value"
                       id="value"
                       class="form-control"
                       value="{{ old('value') }}">
            </div>

            <div class="col-md-6">
                <label>Level</label>
                <input type="number"
                       name="level"
                       id="level"
                       class="form-control"
                       value="{{ old('level') }}">
            </div>

            <div class="col-md-6">
                <label>Parent Key</label>
                <input type="number"
                       name="parentKey"
                       id="parentKey"
                       class="form-control"
                       value="{{ old('parentKey') }}">
            </div>

            <div class="col-md-6">
                <label>Top Value</label>
                <input type="number"
                       name="topValue"
                       id="topValue"
                       class="form-control"
                       value="{{ old('topValue') }}">
            </div>

            <div class="col-md-6">
                <label>Child Type</label>
                <input type="text"
                       name="childType"
                       id="childType"
                       class="form-control"
                       value="{{ old('childType') }}">
            </div>

            <div class="col-md-6">
                <label>Label</label>
                <input type="text"
                       name="label"
                       id="label"
                       class="form-control"
                       value="{{ old('label') }}">
            </div>

            <div class="col-md-6">
                <label>Start Time</label>
                <input type="time"
                       name="start_time"
                       class="form-control"
                       value="{{ old('start_time') }}">
            </div>

            <div class="col-md-6">
                <label>End Time</label>
                <input type="time"
                       name="end_time"
                       class="form-control"
                       value="{{ old('end_time') }}">
            </div>

        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary" onclick="showSpinner()">
                Submit
            </button>
        </div>

    </form>

</div>

</div>

<!-- Spinner -->
<div id="spinnerContainer" class="spinner-container">
    <div class="spinner"></div>
</div>

<script>

function cloneType() {

    let groupType = $("#groupname").val();

    $.ajax({
        url: "{{ route('clone') }}",
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            productid: groupType
        },
        success: function(response) {

            if (response.data && response.data.length > 0) {

                let item = response.data[0];

                $("#name").val(item.name ?? '');
                $("#description").val(item.description ?? '');
                $("#groupType").val(item.groupType ?? '');
                $("#value").val(item.value ?? '');
                $("#level").val(item.level ?? '');
                $("#parentKey").val(item.parentKey ?? '');
                $("#topValue").val(item.topValue ?? '');
                $("#childType").val(item.childType ?? '');
                $("#label").val(item.label ?? '');
            }
        }
    });
}

function showSpinner() {
    document.getElementById('spinnerContainer').style.display = 'block';
}

</script>

</body>
</html>