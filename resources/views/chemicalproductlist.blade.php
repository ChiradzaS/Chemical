<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Products List</title>
    

<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<script src="{{ asset('public/build/assets/chemicalproductlist.js ') }}" type="module" ></script> 


 
    


    
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
                    unitName: "{{ $product->unitPackId }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

                // For products data  
        window.product = [
            @foreach($porducts as $product)
                {
                    id: {{ $product->id }},
                    name: "{{ $product->name }}",
                    productType: "{{ $product->productType }}",
                    unitName: "{{ $product->productType }}"
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



                window.vehicleTypesData = [
            @foreach($vehicletypes as $vehicle)
                {
                    id: {{ $vehicle->id }},
                    name: "{{ $vehicle->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


                        window.driverTypesData = [
            @foreach($drivers as $driver)
                {
                    id: {{ $driver->id }},
                    name: "{{ $driver->name }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

//============================================ChemicalTypes=========================================================
        

         window.chemicalTypesData = [
            @foreach($chemicalTypes as $chemicalType)
                {
                    id: {{ $chemicalType->id }},
                    name: "{{ $chemicalType->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];



        window.chemicalTypesData = [
            @foreach($chemicalCustomers as $chemicalCustomer)
                {
                    id: {{ $chemicalCustomer->id }},
                    name: "{{ $chemicalCustomer->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


        window.viscosityData = [
            @foreach($viscosity as $viscosit)
                {
                    id: {{ $viscosit->id }},
                    name: "{{ $viscosit->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


        window.activeIngredientsData = [
            @foreach($activeIngredients as $activeIngredient)
                {
                    id: {{ $activeIngredient->id }},
                    name: "{{ $activeIngredient->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


        window.fragranceData = [
            @foreach($fragrances as $fragrance)
                {
                    id: {{ $fragrance->id }},
                    name: "{{ $fragrance->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];



        window.fragranceData = [
            @foreach($fragrances as $fragrance)
                {
                    id: {{ $fragrance->id }},
                    name: "{{ $fragrance->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


        window.chemicalColoursData = [
            @foreach($chemicalColours as $chemicalColour)
                {
                    id: {{ $chemicalColour->id }},
                    name: "{{ $chemicalColour->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

        window.bottleTypesData = [
            @foreach($bottleTypes as $bottleType)
                {
                    id: {{ $bottleType->id }},
                    name: "{{ $bottleType->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];



        window.containerSizesData = [
            @foreach($containerSizes as $containerSize)
                {
                    id: {{ $containerSize->id }},
                    name: "{{ $containerSize->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];

        window.capTypesData = [
            @foreach($capTypes as $capType)
                {
                    id: {{ $capType->id }},
                    name: "{{ $capType->name }}"

                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];


        window.lableTypesData = [
            @foreach($lableTypes as $lableType)
                {
                    id: {{ $lableType->id }},
                    name: "{{ $lableType->name }}"

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