<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
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
<h3></h3><br>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>

<table class="table table-striped" >
    
<tr><th  style="text-align: center; width: 200px;">Inventory List</th></tr>


<tr>

<th  scope="col"> id</th>
<th  scope="col"> Product</th>
<th  scope="col"> Openning Inventory</th>
<th  scope="col"> Closing Inventory</th>
<th  scope="col"> Updated_at</th>
<th  scope="col"> Created_at</th>



</tr>
@foreach ($stocks as $stock)
@php $tmpProduct = $porducts[$stock->productId]; @endphp
<tr>
<td>{{ $stock->id }}</td>
<td>{{ $tmpProduct->name }}</td>
<td></td>
<td></td>
<td>{{ $stock->updated_at }}</td>
<td>{{ $stock->created_at }}</td>






@endforeach
</table>
{!! $stocks->links() !!}
</body>
</html>