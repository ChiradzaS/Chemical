<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->


<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<!DOCTYPE html>
<html lang="en">
 </body>
</html>

<a class="btn btn-outline-primary" href="{{ route('pickingslip.create') }}"> Create New</a>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>
<br>
<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Completed Picking List</th>



</tr>
</thead>
</table>

 <br>
<table class="table table-striped">
    <thead>
        <tr>
            <th >Customer Name</th>
            <th></th>
            <th></th>
           
            <th colspan="2" style="text-align: center;"></th>
            
        </tr>
    </thead>
    <tbody>
        @foreach ($pickingslips as $pickingslip)
            @php $tmpCust = $customers[$pickingslip->customerId]; @endphp
            @php $orderitem = $pickingslip->orderitemId ; @endphp
            @php $pick = $pickingslip->orderitemId ; @endphp
            @php $products = App\Models\Pickingslip::where('customerId', $pickingslip->customerId)->where('stateId','=',45)->get(); @endphp
            <tr>
                <td >  <strong>{{ $tmpCust->name }}</strong></td>
            
               

        
                <td></td>
                <td></td>
               
              
                @php
                $productsCount = App\Models\Pickingslip::where('customerId', $pickingslip->customerId)->count();
                @endphp
               
                @if ($productsCount > 1)
                <td colspan="2"  style="text-align: center;"></td>
    
                @else
                    <td colspan="2" ></td>
                @endif
            </tr>
            
       

            @foreach ($products as $product)
                @php $tmpProduct = $porducts[$product->productId]; @endphp
                @php $tmpUnittype = $unittypes[$product->unitId]; @endphp
                <tr>
                    <td></td> 
                    <td  style="text-align: center;">{{ $tmpProduct->name }}</td>
                    <td width="8%">{{ $tmpUnittype->name }}</td>
                    <td width="8%">{{ $product->qnt }}</td>
                    <td >{{ $product->updated_at }}
                    
                 </td>

                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

{!! $pickingslips->links() !!}
</body>
</html>