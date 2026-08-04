<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
    <script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
    <title>Chemical Stock</title>
</head>
<body class="bg-light">

<div class="container-fluid py-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0 fw-bold">🧪 Chemical Stock</h4>
            <span class="badge text-bg-primary px-3 py-1" style="font-size:11px; border-radius:20px;">CHEM</span>
        </div>
        <span class="text-muted small">{{ $stocks->total() }} product{{ $stocks->total() !== 1 ? 's' : '' }}</span>
    </div>

    {{-- ── Table ── --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="font-size: 14px;">
                <thead style="background: #1a1a2e; color: white;">
                    <tr>
                        <th class="px-4 py-3" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; width:50px;">#</th>
                        <th class="px-4 py-3" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em;">Product</th>
                        <th class="px-4 py-3" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em;">SKU</th>
                        <th class="px-4 py-3 text-center" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; width:160px;">Prev Qty</th>
                        <th class="px-4 py-3 text-center" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; width:160px;">Current Qty</th>
                        <th class="px-4 py-3 text-center" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; width:180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stocks as $index => $stock)
                    <tr class="{{ $stock->qnt == 0 ? 'table-warning' : '' }}">

                        {{-- Row number --}}
                        <td class="px-4 py-3 text-muted small">
                            {{ $stocks->firstItem() + $loop->index }}
                        </td>

                        {{-- Product name --}}
                        <td class="px-4 py-3">
                            <span class="fw-semibold text-dark">
                                {{ $stock->product_name ?? '—' }}
                            </span>
                            @if($stock->qnt == 0)
                                <span class="badge text-bg-warning ms-2" style="font-size:10px;">Out of stock</span>
                            @elseif($stock->qnt <= 10)
                                <span class="badge text-bg-danger ms-2" style="font-size:10px;">Low stock</span>
                            @endif
                        </td>

                        {{-- SKU --}}
                        <td class="px-4 py-3 text-muted small font-monospace">
                            {{ $stock->product_sku ?? '—' }}
                        </td>

                        {{-- Prev qty --}}
                        <td class="px-4 py-3 text-center text-muted">
                            {{ number_format($stock->prvqnt, 0) }}
                        </td>

                        {{-- Current qty --}}
                        <td class="px-4 py-3 text-center">
                            <span class="fw-bold fs-6 {{ $stock->qnt == 0 ? 'text-warning' : ($stock->qnt <= 10 ? 'text-danger' : 'text-success') }}">
                                {{ number_format($stock->qnt, 0) }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('stocks.create', ['stockId' => $stock->id]) }}"
                               class="btn btn-sm btn-outline-primary px-3">
                                📋 History
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div style="font-size: 40px;">🧪</div>
                            <p class="mt-2 mb-0">No stock records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Pagination ── --}}
    <div class="mt-3 d-flex justify-content-end">
        {!! $stocks->links() !!}
    </div>

</div>

</body>
</html>