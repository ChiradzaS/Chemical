@extends('dashboard')  

@section('content')
@if(session('status')) 
<div class="alert alert-success mb-1 mt-1"> 
    {{ session('status') }} 
</div>
@endif   

<main class="login-form"  >
    <div class="cotainer">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <h3 class="card-header text-center">Login</h3>
                    <div class="card-body">
                        <form method="POST" action="{{ route('login.custom') }}" autocomplete="off">
                            @csrf
                            <div class="form-group mb-3">
                                <input type="text"  placeholder="name" id="name"   class="form-control" name="name" required autofocus>
                                @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <input type="password" placeholder="Password"   id="password" class="form-control" name="password" required>
                                @if ($errors->has('password'))
                                <span class="text-danger">{{ $errors->first('password') }}</span>
                                @endif
                            </div>
                            
                            <div class="d-grid mx-auto">
                                <button type="submit" class="btn btn-dark btn-block rounded-custom"  >Log In</button>
                            </div>
                            <br>
                            <div>
                                <button type="button" class="btn btn-secondary" onclick="history.back();">Back</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
    <script>
      window.onload = function() {
      
        document.getElementById('name').value = '';
        document.getElementById('password').value = '';
      };
    </script>

<style>

.rounded-custom {
        border-radius: 20px; /* Adjust the value as needed */
    }

body, html {
    margin: 0;
    padding: 0;
    height: 100%;
}

.login-form {
    margin: 0;
    padding: 0;
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image: url('public/images/packaging-background.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
}

.cotainer {
    margin: 0;
    padding: 0;
    width: 100%;
}

.card {
    margin: 0 auto;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    background-color: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 15px;
}
    /* .login-form {
        min-height: 85vh;
        display: flex;
        align-items: center;
        background-image: url('public/images/packaging-background.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
    } */

    /* Fallback background in case image fails to load */
    .login-form::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #0d6efd, #012a72);
        z-index: -2;
    }

    /* Dark overlay */
    .login-form::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        z-index: -1;
    }

    .login-form .cotainer {
        position: relative;
        z-index: 1;
        width: 100%;
    }

    .login-form .card {
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        background-color: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
    }

    .login-form .card-header {
        background-color: rgba(4, 4, 4, 0.7);
        color: white;
        font-weight: 600;
        border-bottom: none;
        border-radius: 15px 15px 0 0;
    }

    .login-form .card-body {
        padding: 30px;
    }

    .login-form .form-control {
        background-color: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.5);
        color: white;
    }

    .login-form .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .login-form .form-control:focus {
        background-color: rgba(255, 255, 255, 0.3);
        border-color: white;
        box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        color: white;
    }

    .login-form .btn-dark {
        background-color: rgba(0, 0, 0, 0.7);
        border-color: rgba(255, 255, 255, 0.5);
        color: white;
        transition: all 0.3s ease;
    }

    .login-form .btn-dark:hover {
        background-color: rgba(13, 110, 253, 0.9);
        border-color: white;
    }

    .login-form .btn-secondary {
        background-color: rgba(108, 117, 125, 0.7);
        border-color: rgba(108, 117, 125, 0.5);
        color: white;
    }

    .login-form .btn-secondary:hover {
        background-color: rgba(7, 8, 8, 0.9);
        border-color: rgba(0, 0, 0, 0.5);
    }

    .login-form .text-danger {
        color: #fff !important;
        background-color: rgba(220, 53, 69, 0.7);
        padding: 5px 10px;
        border-radius: 5px;
        margin-top: 5px;
        display: inline-block;
    }
</style>
@endsection