<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >



  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font=awesome.min.css" >



  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.19.0/font/bootstrap-icons.css" rel="stylesheet">



  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">



  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>



  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>



  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>



  <meta name="viewport" content="width=device-width, initial-scale=1.0">


  <meta name="csrf-token" content="{{ csrf_token() }}">







<!-- Script CDN -->



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>







<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>



<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <title>Customer Prices</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script type='text/javascript'>

 var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


 function deleteItem(itemId) {
    if (confirm("Are you sure you want to delete this item?")) {

        //alert('hoyooooooo');
        $.ajax({


url: "{{ route('deleteprice') }}",
type: 'post',
data: {_token: CSRF_TOKEN, itemId:itemId},
dataType: 'json',
success: function(response){

    try {


        Swal.fire({
        title: 'Product deleted....',
        icon: 'success',
        showConfirmButton: true,
        timer: 1000,
        position: 'center',
        confirmButtonText: 'OK'
    }).then(() => {
        location.reload();  // Reload the page after the alert
    });


        //window.reload();
        
        // Additional success handling if needed
       
        
    } catch (error) {
      
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while processing the pricing. Please try again.',
            icon: 'error',
            showConfirmButton: true,
            position: 'center',
            confirmButtonText: 'OK'
        });
    }


   }


    });
    }
}


</script>






  </div>
</div>  

</form>
<hr>



@php
    // Ensure $setprices is a collection
    $setprices = collect($setprices);
    // Group prices by customerId
    $groupedPrices = $setprices->groupBy('customerId');
@endphp

@foreach ($groupedPrices as $customerId => $customerPrices)
    @php
        // Get customer details or default to 'N/A'
        $tmpCustomer = $customers[$customerId] ?? null;
    @endphp

    <table>
    <thead class="thead-light">
            <tr class="thead-light">
                <th class="h1 thead-light">{{ $tmpCustomer ? $tmpCustomer->name : 'N/A' }}</th>
                <th class="thead-light"></th>
                <th class="thead-light"></th>
                <th class="thead-light"></th>
            </tr>
        </thead>
    </table>

    <table class="table table-bordered table-sm">
        <tbody>
            @foreach ($customerPrices as $prices)
                @php
                    // Get related data or default to null
                    $tmpColourType = $colourtypes[$prices['colourId']] ?? null;
                    $tmpbagType = $bagtypes[$prices['bagType']] ?? null;
                    $tmpMaterialType = $materialtypes[$prices['material']] ?? null;
                    $tmpUnitType = $unittypes[$prices['unitId']] ?? null;
                @endphp

                <tr>
                    <td class="product">
                        @if($prices['gusset'] > 0)
                          <strong>  {{ $prices['width'] }}({{ $prices['gusset'] / 2 }} + {{ $prices['gusset'] / 2 }})mm x {{ $prices['length'] }}mm x {{ $prices['actualMicron'] }}mic {{ $tmpColourType ? $tmpColourType->name : 'N/A' }}</strong>
                        @else
                        <strong>{{ $prices['width'] }}mm x {{ $prices['length'] }}mm x {{ $prices['actualMicron'] }}mic {{ $tmpColourType ? $tmpColourType->name : 'N/A' }}</strong>
                        @endif
                    </td>
                    <td class="unit-type">{{ $tmpUnitType ? $tmpUnitType->name : 'N/A' }}</td> 
                    <td class="bag-type">{{ $tmpbagType ? $tmpbagType->name : 'N/A' }}</td>
                    <!-- <td style="width: 500px;">{{ $tmpColourType ? $tmpColourType->name : 'N/A' }}</td> -->
                    <td class="material-type">{{ $tmpMaterialType ? $tmpMaterialType->name : 'N/A' }}</td> 
                  
                    <td class="current-price">Current  price <strong >R{{ $prices['price']  }}</strong></td> 
                    <td class="previous-price">Previous price R{{ $prices['prv_prices'] ?? '--' }}</td> 
                    <td class="actualMicron">
              
                    @ Display mic  <strong> ( {{ $prices['actualMicron'] }} mic ) -> R {{ $prices['price2'] ?? 0 }} / kg</strong>
                     
                    </td>
                    <td class="micron" >@ Real mic  <strong> ( {{ $prices['micron'] }} mic ) -> R {{ $prices['pricePerKg'] ?? 0 }} / kg</strong></td>
             
               
                    <td class="date">
                         
                          <a class="btn btn-outline-info  btn-sm" href="{{ route('setprices.edit', $prices['id']) }}">Update</a>  &nbsp; <a href="#" class="btn btn-outline-danger  btn-sm" onclick="deleteItem('{{ $prices['id'] }}')">Delete</a> 
                        </td>
                    <!-- <td style="width: 500px;">{{ $prices['created_at'] }}</td> -->
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

<style>


.product {
    width: 17%;
}

.unit-type {
    width: 5%;
}
.bag-type, .material-type {
    width: 7%;
}
.current-price, .previous-price {
    width: 9%;
}
.current-price {
    color: #0d2d81;
}
.previous-price {
    color: green;
}
.actions {
    width: 15%;
}

.micron {
    width: 15%;
}

.actualMicron {
    width: 15%;
}


.date {
    width: 8%;
}






</style>

</body>
<script>


        
    </script>
</html>