<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
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


</head>
<body>
  <div id="root"></div>
    
      <script src="{{ asset('public/build/assets/stockreport.js ') }}" type="module" ></script> 
</body>
</html>