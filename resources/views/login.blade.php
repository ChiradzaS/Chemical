<!DOCTYPE html>
<html lang="en">
<head>
<title>Buzz Chem
  
</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#ffffff">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}?v={{ @filemtime(public_path('build/assets/app.css')) }}">

<style>
  html, body { margin:0; height:100%; background:#f1f5f9; -webkit-text-size-adjust:100%; }
  #root      { min-height:100%; min-height:100dvh; display:flex; flex-direction:column; }
  #root > *  { flex:1; }
</style>

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
  <script type="module" src="{{ asset('public/build/assets/LoginPage.js') }}?v={{ @filemtime(public_path('build/assets/LoginPage.js')) }}"></script>
</body>
</html>