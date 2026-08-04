<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel React App</title>
    


{{-- Use the compiled assets instead --}}
<link rel="stylesheet" href="{{ asset('public/build/assets/app-beAh6437.css') }}">
<script src="{{ asset('public/build/assets/jobcardReport-ahEP0-5b.js') }}" type="module" ></script>
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
            

</script>
    <!-- <h1>Laravel + React Test</h1>
    <p>Laravel is working ✅</p>
    <p id="react-status">React loading...</p>
     -->
    <div id="root"></div>
    
    <script>
        // console.log('=== CHECKING REACT SETUP ===');
        // console.log('Root div:', document.getElementById('root'));
        
        // Check if Vite assets loaded
        setTimeout(() => {
            //console.log('Checking React after delay...');
            // console.log('React available:', typeof window.React !== 'undefined');
            
            const rootContent = document.getElementById('root').innerHTML;
            // console.log('Root content:', rootContent);
            
            if (rootContent.trim() === '') {
                document.getElementById('react-status').innerHTML = 'React NOT loaded ❌';
                // console.error('React failed to mount!');
            } else {
                document.getElementById('react-status').innerHTML = 'React loaded ✅';
                // console.log('React mounted successfully!');
            }
        }, 2000);
    </script>
    
</body>
</html>