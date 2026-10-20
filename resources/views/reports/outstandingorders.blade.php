<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Outstanding Orders — Buzz Chem</title>

<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<style>
  html, body { margin: 0; background: #f3f4f6; }
</style>

<script>
  window.laravelApiUrl = "{{ url('') }}";
</script>



</head>
<body>
  <div id="root"></div>
       
  <script type="module" src="{{ asset('public/build/assets/outstandingorders.js') }}?v={{ @filemtime(public_path('build/assets/outstandingorders.js')) }}"></script>

        
</body>
</html>