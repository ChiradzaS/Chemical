<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Raw Material Stock — Buzz Chem</title>

<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<style>
  html, body { margin: 0; background: #f3f4f6; }
</style>

<script>
  window.laravelApiUrl = "{{ url('') }}";
</script>

@php
    // Cache-buster: changes whenever the built file changes
    $entry = public_path('build/assets/RawMaterialStock.js');
    $ver   = file_exists($entry) ? filemtime($entry) : time();
@endphp
<!-- <script type="module" src="{{ asset('public/build/assets/RawMaterialStock.js') }}?v={{ $ver }}"></script> -->
</head>
<body>
  <div id="root"></div>
<script type="module" src="{{ asset('public/build/assets/rawmaterialstock.js') }}"></script>
</body>
</html>