<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<title>Sales Report — Buzz Chem</title>

<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<style>
  html, body { margin: 0; background: #f3f4f6; }
  @page { size: A4 landscape; margin: 10mm; }
  @media print { html, body { background: #fff; } }
</style>

<script>
  window.laravelApiUrl = "{{ url('') }}";
  window.customersData = [
    @foreach ($customers as $customer)
      { id: {{ (int) $customer->id }}, name: {!! json_encode((string) $customer->name, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!} }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ];
</script>

@php
    // Cache-buster: changes whenever the built file changes
    $entry = public_path('build/assets/SalesReport.js');
    $ver   = file_exists($entry) ? filemtime($entry) : time();
@endphp
<script type="module" src="{{ asset('public/build/assets/SalesReport.js') }}?v={{ $ver }}"></script>
</head>
<body>
  <div id="root"></div>
  <noscript>Enable JavaScript to use this page.</noscript>
</body>
</html>