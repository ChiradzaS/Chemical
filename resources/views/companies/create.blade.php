<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Company Form - Laravel 8 CRUD</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

</head>
<body>
    <div>
{{-- @include('view') --}}
<br>
</div>
<div class="container mt-2">
<div class="row">
    <div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Add Company</h2>
</div>


<div class="pull-right">

</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('companies.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Company Name:</strong>
<input type="text" name="name"  class="form-control form-control-sm" placeholder="Company Name">
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Company Email:</strong>
<input type="email" name="email" class="form-control form-control-sm" placeholder="Company Email">
@error('email')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Company Address:</strong>
<input type="text" name="address" class="form-control form-control-sm" placeholder="Company Address">
@error('address')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>






<div class="container">
  <div class="row">
    <div class="col-md-6">
      <table class="table">
        <thead>
          <tr>
            <th>Table 1 Header</th>
            
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Row 1, Column 1</td>
          </tr>
          <tr>
            <td>Row 2, Column 1</td>
          </tr>
          <!-- Add more rows as needed -->
        </tbody>
      </table>
    </div>
    <div class="col-md-6">
      <table class="table">
        <thead>
          <tr>
            <th>Table 2 Header</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Row 1, Column 2</td>
          </tr>
          <tr>
            <td>Row 2, Column 2</td>
          </tr>
          <!-- Add more rows as needed -->
        </tbody>
      </table>
    </div>
  </div>
</div>






<br>
<button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Submit</button> 
</div>
</form>
<style>


.container1 {
   
  background-color: #f0f0f0;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 60%;
    max-height: 300px; /* Set the max height of the container */
    overflow-y: auto; /* Enable vertical scrolling */
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

tr:hover {
    background-color: #f5f5f5;
}

</style>
<br>
<div class="container1">
        <table>
            <thead>
                <tr>
                    <th>Header 1</th>
                    <th>Header 2</th>
                    <th>Header 3</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Item 1</td>
                    <td>Item 2</td>
                    <td>Item 3</td>
                </tr>
                <tr>
                    <td>Item 4</td>
                    <td>Item 5</td>
                    <td>Item 6</td>
                </tr>
                <tr>
                    <td>Item 7</td>
                    <td>Item 8</td>
                    <td>Item 9</td>
                    <tr>
                    <td>Item 1</td>
                    <td>Item 2</td>
                    <td>Item 3</td>
                </tr>
                <tr>
                    <td>Item 4</td>
                    <td>Item 5</td>
                    <td>Item 6</td>
                </tr>
                <tr>
                    <td>Item 7</td>
                    <td>Item 8</td>
                    <td>Item 9</td>
                </tr>
                </tr>
            </tbody>
        </table>
    </div>

</html>