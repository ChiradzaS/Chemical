<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>create job-card</title>

      <!-- @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/createjobcard.tsx']) -->

    



{{-- Use the compiled assets instead --}}
<link rel="stylesheet" href="{{ asset('public/build/assets/app-beAh6437.css') }}">
<script src="{{ asset('public/build/assets/createjobcard-CuQ8z3br.js') }}" type="module" ></script> 

</head>
<body>
<script>

    window.customersData = [

        @foreach($customers as $customer)
            {
                id: {{ $customer->id }},
                name: "{{ $customer->name }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach

    ];

        // For products data  
        window.productsData = [
            @foreach($products as $product)
                {
                    id: {{ $product->id }},
                    name: "{{ $product->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

                // For products data  
        window.product = [
            @foreach($porducts as $product)
                {
                    id: {{ $product->id }},
                    name: "{{ $product->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

        // For machine types data
        window.machineTypesData = [
            @foreach($machinetypes as $machineType)
                {
                    id: {{ $machineType->id }},
                    name: "{{ $machineType->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


                // For unittypes data
        window.unitTypesData = [
            @foreach($unittypes as $unitType)
                {
                    id: {{ $unitType->id }},
                    name: "{{ $unitType->name }}",
                    value: "{{ $unitType->value }}",
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


               // For materialtypes data
        window.materialTypesData = [
            @foreach($materialtypes as $materialType)
                {
                    id: {{ $materialType->id }},
                    name: "{{ $materialType->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

        
               // For colourtypes data
        window.colourTypesData = [
            @foreach($colourtypes as $colourType)
                {
                    id: {{ $colourType->id }},
                    name: "{{ $colourType->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


                
               // For colourtypes data
        window.bagTypesData = [
            @foreach($bagtypes as $bagType)
                {
                    id: {{ $bagType->id }},
                    name: "{{ $bagType->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


                       // For colourtypes data
        window.processTypesData = [
            @foreach($processtypes as $processType)
                {
                    id: {{ $processType->id }},
                    name: "{{ $processType->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

 
// Get URL parameters and store in object (not array)
const urlParams = new URLSearchParams(window.location.search);
window.urlParamsData = {
    customerId: urlParams.get('customerId') || '',
    productId: urlParams.get('productId') || ''
};

//console.log('URL Params:', window.urlParamsData); 
  
</script>
    <!-- <h1>Laravel + React Test</h1>
    <p>Laravel is working ✅</p>
    <p id="react-status">React loading...</p>
     -->
    <div id="root"></div>
    
    <script>

    setTimeout(() => {
        const rootContent = document.getElementById('root').innerHTML;
        //console.log('Root content:', rootContent);
        
        if (rootContent.trim() === '') {
            console.error('React NOT loaded ❌');
        } else {
            console.log('React loaded ✅');
            
        }
    }, 2000);
</script>