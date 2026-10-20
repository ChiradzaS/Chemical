<!DOCTYPE html>
<html lang="en">
<head>
<title>BuzzChem — Sign In</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#000000">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<style>html,body{margin:0;background:#171717;-webkit-text-size-adjust:100%;}</style>

<script>
  window.laravelApiUrl    = "{{ url('') }}";
  window.csrfToken        = "{{ csrf_token() }}";
  window.loginAction      = "{{ route('login.custom') }}";
  window.loginOldEmail    = "{{ addslashes(old('email', '')) }}";
  window.loginOldRemember = {{ old('remember') ? 'true' : 'false' }};
  window.loginSuccess     = "{{ addslashes(session('success', '')) }}";
  window.loginErrors      = [
    @foreach ($errors->all() as $err)
      "{{ addslashes($err) }}",
    @endforeach
  ];
</script>
</head>
<body>
  <div id="root"></div>
  <script type="module" src="{{ asset('public/build/assets/loginpage.js') }}"></script>
</body>
</html>