
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Types</title>

    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">

    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <script src="{{ asset('public/js/select2.min.js') }}"></script>

    <style>
        body {
            background-color: #f4f6f9;
        }

        .page-card {
            border: none;
            border-radius: 12px;
        }

        .table th {
            white-space: nowrap;
        }

        .badge-group {
            font-size: 0.85rem;
            padding: 6px 10px;
        }

        .alert {
            border-radius: 10px;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }
    </style>
</head>

<body>

<div class="container-fluid py-4">

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <strong>Success!</strong> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card page-card shadow">

        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Type Management</h3>

            <a class="btn btn-light" href="{{ route('types.create') }}">
                + Create New Type
            </a>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Value</th>
                        <th>Level</th>
                        <th>Parent Key</th>
                        <th>Group Type</th>
                        <th>Top Value</th>
                        <th>Child Type</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th width="150">Actions</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($types as $type)

                        <tr>
                            <td>{{ $type->id }}</td>

                            <td>
                                <strong>{{ $type->name }}</strong>
                            </td>

                            <td>{{ $type->description }}</td>

                            <td>{{ $type->value }}</td>

                            <td>{{ $type->level }}</td>

                            <td>{{ $type->parentKey }}</td>

                            <td>
                                <span class="badge bg-info text-dark badge-group">
                                    {{ $type->groupType }}
                                </span>
                            </td>

                            <td>{{ $type->topValue }}</td>

                            <td>{{ $type->childType }}</td>

                            <td>{{ $type->start_time }}</td>

                            <td>{{ $type->end_time }}</td>

                            <td>

                                <div class="d-flex gap-2">

                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('types.edit',$type->id) }}">
                                        Edit
                                    </a>

                                    {{-- Uncomment if delete is needed

                                    <form action="{{ route('types.destroy',$type->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this type?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>

                                    --}}

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="12" class="text-center py-4">
                                No types found.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script src="{{ asset('public/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>
