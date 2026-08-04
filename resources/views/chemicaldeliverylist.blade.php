<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chemical Invoices &amp; Deliveries</title>


<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<script src="{{ asset('public/build/assets/chemicaldeliverylist.js') }}" type="module" ></script> 



</head>
<body>
<script>

    window.laravelApiUrl = "{{ url('') }}/Chemical";

    window.customersData = [
        @foreach($customers as $customer)
            {
                id:   {{ $customer->id }},
                name: "{{ addslashes($customer->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    window.chemicalProductsData = [
        @foreach($chemicalProducts as $product)
            {
                id:   {{ $product->id }},
                name: "{{ addslashes($product->name) }}",
                sku:  "{{ addslashes($product->sku ?? '') }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    window.containerSizesData = [
        @foreach($containerSizes as $container)
            {
                id:   {{ $container->id }},
                name: "{{ addslashes($container->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

</script>

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