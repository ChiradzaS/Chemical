<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

    <meta http-equiv="refresh" content="3600"> 
</head>
<body>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<nav class="navbar navbar-light navbar-expand-lg mb-5" style="background-color: #000000;">
    <div class="container" >
        <a class="navbar-brand mr-auto" style="color: white; text-align: right;" href="#">
            <h3 class="mb-3"><span style="font-size: 1.9em;">S</span>ailing Packaging</h3>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}" style="color: white;"></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register-user') }}" style="color: white;"></a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="{{ route('login') }}" style="margin-top: 8px;">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="{{ route('register-user') }}" style="margin-top: 8px;">Register</a>
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('signout') }}">Logout</a>
                </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
@yield('content')
</body>
</html>
