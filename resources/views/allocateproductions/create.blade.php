<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Production List</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="card shadow-lg">
        <div class="card-body">
            <h2 class="mb-4">Detailed Production Items</h2>

            <div class="mb-3">

            @php

            $tmpuser = $user[ $userId ] ?? null; 

            @endphp
            <p><strong>Employee:</strong> {{  $tmpuser->name ?? 'Unknown'  }}</p>
            <p><strong>Shift:</strong> {{ $shiftId == 31 ? 'Day' : 'Night' }}</p>
            <p><strong>Date:</strong> {{ $prodDate }}</p>
  
            
            </div>




            @if($items->isEmpty())
                <div class="alert alert-warning">No production items found for the selected shift and date.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">

             
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Weight</th>
                                <th>Created At</th>
                                <th>Machine</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            @php

                                $tmpProduct = $chemicalProducts[  $item->productId ] ?? null; 
                                $tmpMachine = $machinetypes[  $item->machineId ] ?? null; 

                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{  $tmpProduct->name }}</td>
                                    <td>{{ $item->weight }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('j D Y, h:i A') }}</td>
                                    <td>{{ $tmpMachine->name }}</td>

                             
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="card-body">
    <a href="javascript:history.back()" class="btn btn-secondary btn-lg mb-3">
        &larr; Back
    </a>


</div>
</div>




<!-- Bootstrap JS (Optional, for dropdowns etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
