<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jobcard React</title>
    
        <!-- @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/reactjoblist.tsx']) -->

{{-- Use the compiled assets instead --}}
<link rel="stylesheet" href="{{ asset('public/build/assets/app-beAh6437.css') }}">
<script src="{{ asset('public/build/assets/reactjoblist-BrP6lOiM.js') }}" type="module" ></script>
        <!-- @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/reactjoblist.tsx']) -->
    
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
                    name: "{{ $product->name }}",
                    productType: "{{ $product->productType }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

                // For products data  
        window.product = [
            @foreach($porducts as $product)
                {
                    id: {{ $product->id }},
                    name: "{{ $product->name }}",
                    productType: "{{ $product->productType }}"
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


        

         window.stateTypesData = [
            @foreach($statustypes as $stateType)
                {
                    id: {{ $stateType->id }},
                    name: "{{ $stateType->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];




            
</script>

    <div id="root"></div>
    
    <script>
     
        
      
        setTimeout(() => {
        
            
            const rootContent = document.getElementById('root').innerHTML;
       
            
            if (rootContent.trim() === '') {
                document.getElementById('react-status').innerHTML = 'React NOT loaded ❌';
             
            } else {
                document.getElementById('react-status').innerHTML = 'React loaded ✅';
                
            }
        }, 2000);
    </script>
</body>
</html>