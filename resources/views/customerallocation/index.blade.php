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
<meta charset="UTF-8">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font=awesome.min.css" >
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.19.0/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Script CDN -->

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>


<script type='text/javascript'>
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    function setcustomer(user) {

        var userResponse = confirm("Are you sure you have selected the right Customer for the user?");

        if (userResponse) {
            var CustomerId = document.getElementById("customers." + user).value;
            var userId = document.getElementById("user." + user).value;

            document.getElementById("cust." + user).value = CustomerId;
            document.getElementById("user." + user).value = userId;

            $.ajax({
                url: "{{ route('allocatecustomer') }}",
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    user: user,
                    CustomerId: CustomerId
                },
                dataType: 'json',
                success: function (response) {

                    refreshPage();
                }
            });
        } else {

            return false;

        }

        function refreshPage() {
    location.reload();
}

    }
</script>



<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
    <br>
    <br>
<h3 class="centred">Allocate Users to Customer</h3>
<br>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>

<br>
<form action="{{ route('customerallocation.index') }}" method="GET" enctype="multipart/form-data">
<nav class="navbar navbar-light bg-light ">
  <form class="form-inline">
  <div class="form-group">
            <select name="srchbycust" id=""    class="js-example-basic-single"    placeholder="-- Select Customer --">
            <option value="" disabled selected hidden>-- select customer name --</option>
            @foreach($customers as $customer)
            <option value="{{ $customer->id }}"  >{{ $customer->name }}</option>
            @endforeach
            </select>
            </div>&nbsp;&nbsp;
    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">&nbsp;&nbsp;Search&nbsp;&nbsp;</button>
  </form>

</nav>
<br>
<br>
<table class="table table-striped" >
<tr>
<th  scope="col"> #</th>
<th  scope="col"> Name</th>
<th  scope="col">Company Name</th>
<th  scope="col">User Email</th>
<th  scope="col">Created_Date</th>
<th  scope="col">Customer</th>
<th  scope="col">Remove </th>

</tr>
@foreach ($users as $user)

@if($user->company)



<tr style="background-color: #CBF6E6;" >
<td>{{$user->id }}</td>
<td>{{ $user->name }}</td>
<td>{{ $user->companyName }}</td>
<td>{{ $user->email }}</td>
<td>{{ $user->created_at }}</td>

<input type="hidden" name="user" value="{{$user->id }}">

<td>
<select name="customer" id="customers.{{$user->id }}"   class="form-control form-control-sm"  onchange="setcustomer({{$user->id }})"  disabled>
@foreach($customers as $customer)
<option value="{{ $customer->id }}" @if ($user->company == $customer->id) selected @endif>{{ $customer->name }}</option>

@endforeach
</select>
</td>

<td>


<a class="btn btn-secondary btn-sm" href="{{ route('customerallocation.edit',$user->id) }}">Remove</a>

</td>


</tr>

@else




<tr style="background-color: #FED2E1;" >
<form action="{{ route('customerallocation.create') }}" method="GET" enctype="multipart/form-data">

<td>{{$user->id }}</td>
<td>{{ $user->name }}</td>
<td>{{ $user->companyName }}</td>
<td>{{ $user->email }}</td>
<td>{{ $user->created_at }}</td>

<td>
<div class="form-group">
<select name="customer" id="customers.{{$user->id }}"   class="form-control form-control-sm"  onchange="setcustomer({{$user->id }})"  placeholder="-- Select Customer --">
<option value="" disabled selected hidden>-- select customer name --</option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}"  >{{ $customer->name }}</option>
@endforeach
</select>
</div></td>
<td>
<input type="hidden" name="user"  id="user.{{$user->id }}" >
<input type="hidden" id="cust.{{$user->id }}" name="cust" > 


</td>



</tr>

@endif

@endforeach
</table>



</form>

</body>
</html>