<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Receive raw material</title>

    {{-- ── BUILT ASSETS (npm run build) ────────────────────────────────── --}}
    @php
        $js  = public_path('build/assets/receivestock.js');
        $css = public_path('build/assets/app.css');
    @endphp

    <link rel="stylesheet"
          href="{{ asset('public/build/assets/app.css') }}?v={{ file_exists($css) ? filemtime($css) : time() }}">

    <script type="module"
            src="{{ asset('public/build/assets/receivestock.js') }}?v={{ file_exists($js) ? filemtime($js) : time() }}"></script>

    {{-- ── DEV SERVER (npm run dev) ────────────────────────────────────────
         Comment out the two tags above and uncomment these two to test.
         Remember to swap back before you build.

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/receivestock.tsx'])
    --}}
</head>
<body>

<script>
    // The only global this page reads.
    window.ChemicalSupplier = [
        @foreach($suppliers as $supplier)
            {
                id: {{ $supplier->id }},
                code: "{{ addslashes($supplier->code) }}",
                name: "{{ addslashes($supplier->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];
</script>

<div id="root"></div>

<script>
    setTimeout(() => {
        if (document.getElementById('root').innerHTML.trim() === '') {
            console.error('React did not mount — check the Network tab for receivestock.js');
        }
    }, 2000);
</script>

</body>
</html>