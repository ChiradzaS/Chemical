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
{{-- @include('view') --}}
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<!DOCTYPE html>
<html lang="en">
 </body>
</html>
<br>
<h3>Add Packaging</h3>
<br>
<br>

</div>
</div>
</div>

<form action="{{ route('packages.index')}}" method="GET">
<table class="table table-striped" >

<style>
    .table table-striped{
        width: 10%;
}
</style>

<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>



<td width="200px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    
    <strong>Product:</strong>
  </div>&nbsp
  <select  id="productId" name="productId" class="js-example-basic-single" placeholder="-- Select Product --">
<option value="" disabled selected hidden>-- select product name --</option>
@foreach($products as $product)
<option value="{{ $product->id }}" >{{ $product->name }}</option>
@endforeach
</select>
</div>
</td>








<td width="100px">
 <button type="submit" value="query" name="action" class="form-control  btn-dark" >Search</button>
</td>


</form>
<br>
<br>
<table class="table table-striped" width="100%">
<tr>
<th  scope="col"> Id</th>
<th  scope="col"> Name</th>
<th  scope="col"> description</th>
<th  scope="col"> Weight</th>
<th  scope="col"> unit Type</th>

<th  scope="col" width="200px"> Action </th>
</tr>
@foreach ($porducts as $porduct)
@php $tmpUnittype = $unittypes[$porduct->unitTypeId]; @endphp
@php $tmpProduct = $porducts[$porduct->productTypeId]; @endphp


<tr>
<td>{{$porduct->id }}</td>
<td>{{ $porduct->name }}</td>
<td>{{ $porduct->description }}</td>
<td>{{ $porduct->WeightPerProduct }}</td>
<td>{{ $tmpUnittype ->name }}</td>

<td>
<a class="btn btn-outline-info" href="{{ route('packages.create',['productId'=>$porduct->id]) }}">Add</a>
</td>
</tr>
@endforeach
</table>
{!! $porducts->links() !!}
</body>
</html>