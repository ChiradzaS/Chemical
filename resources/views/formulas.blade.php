<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chemical Formulas</title>




                 <link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<script type="module"
        src="{{ asset('public/build/assets/chemicalformula.js') }}?v={{ filemtime(public_path('build/assets/receivestock.js')) }}"></script>


        


</head>
<body>



<script>
    window.chemicalTypesData = [
        @foreach($chemicalTypes as $chemicalType)
            { id: {{ $chemicalType->id }}, name: "{{ addslashes($chemicalType->name) }}" }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];
    window.laravelApiUrl = "{{ url('') }}";
</script>

<div id="root"></div>



    <script>
        setTimeout(() => {
            const rootContent = document.getElementById('root').innerHTML;
            if (rootContent.trim() === '') {
                console.error('React NOT loaded ❌');
            } else {
                console.log('React loaded ✅');
            }
        }, 2000);
    </script>
</body>
</html>