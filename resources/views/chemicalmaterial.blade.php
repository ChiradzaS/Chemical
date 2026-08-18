<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Chemical Raw Materials</title>



                <link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<script type="module"
        src="{{ asset('public/build/assets/chemicalmaterial.js') }}?v={{ filemtime(public_path('build/assets/receivestock.js')) }}"></script>

        


</head>
<body>
<script>

    // ── Customers (brand) ──────────────────────────────────────────────────────
    window.customersData = [
        @foreach($customers as $customer)
            {
                id: {{ $customer->id }},
                name: "{{ addslashes($customer->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Chemical products ──────────────────────────────────────────────────────
    window.chemicalProductsData = [
        @foreach($chemicalProducts as $product)
            {
                id:                    {{ $product->id }},
                name:                  "{{ addslashes($product->name) }}",
                sku:                   "{{ $product->sku ?? '' }}",
                category:              "{{ $product->category ?? '' }}",
                brand:                 "{{ $product->brand ?? '' }}",
                stock_unit_id:         {{ $product->stock_unit_id ?? 'null' }},
                colour_id:             {{ $product->colour_id ?? 'null' }},
                viscosity_id:          {{ $product->viscosity_id ?? 'null' }},
                active_ingredient_id:  {{ $product->active_ingredient_id ?? 'null' }},
                fragrance_id:          {{ $product->fragrance_id ?? 'null' }},
                bag_type_id:           {{ $product->bag_type_id ?? 'null' }},
                container_size_id:     {{ $product->container_size_id ?? 'null' }},
                batch_size_litres:     {{ $product->batch_size_litres ?? 'null' }},
                units_per_batch:       {{ $product->units_per_batch ?? 'null' }},
                yield_percentage:      {{ $product->yield_percentage ?? 'null' }},
                weight_per_unit_grams: {{ $product->weight_per_unit_grams ?? 'null' }},
                price:                 {{ $product->price ?? 'null' }},
                vat_applicable:        {{ $product->vat_applicable ?? 0 }},
                concentration:         {{ $product->concentration ?? 'null' }},
                dilution_ratio:        "{{ $product->dilution_ratio ?? '' }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Unit types ─────────────────────────────────────────────────────────────
    window.unitTypesData = [
        @foreach($unittypes as $unitType)
            {
                id:    {{ $unitType->id }},
                name:  "{{ addslashes($unitType->name) }}",
                value: "{{ $unitType->value ?? 1 }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Colour types ───────────────────────────────────────────────────────────
    window.colourTypesData = [
        @foreach($colourtypes as $colourType)
            {
                id:   {{ $colourType->id }},
                name: "{{ addslashes($colourType->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Viscosity ──────────────────────────────────────────────────────────────
    window.viscosityData = [
        @foreach($viscosity as $viscosit)
            {
                id:   {{ $viscosit->id }},
                name: "{{ addslashes($viscosit->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Active ingredients ─────────────────────────────────────────────────────
    window.activeIngredientsData = [
        @foreach($activeIngredients as $activeIngredient)
            {
                id:   {{ $activeIngredient->id }},
                name: "{{ addslashes($activeIngredient->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Fragrances ─────────────────────────────────────────────────────────────
    window.fragranceData = [
        @foreach($fragrances as $fragrance)
            {
                id:   {{ $fragrance->id }},
                name: "{{ addslashes($fragrance->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Bottle / container types ───────────────────────────────────────────────
    window.bottleTypesData = [
        @foreach($bottleTypes as $bottleType)
            {
                id:   {{ $bottleType->id }},
                name: "{{ addslashes($bottleType->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Container sizes ────────────────────────────────────────────────────────
    window.containerSizesData = [
        @foreach($containerSizes as $containerSize)
            {
                id:   {{ $containerSize->id }},
                name: "{{ addslashes($containerSize->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Process types ──────────────────────────────────────────────────────────


    window.processTypesData = [
    @foreach($chemicalprocesstypes as $processType)
        {
            id:   {{ $processType->id }},
            name: "{{ $processType->name }}"
        }{{ !$loop->last ? ',' : '' }}
    @endforeach
];

    // ── Chemical types (category) ──────────────────────────────────────────────
    window.ChemicalUnitType = [
        @foreach($ChemicalUnitTypes as $ChemicalUnitType)
            {
                id:   {{ $ChemicalUnitType->id }},
                name: "{{ $ChemicalUnitType->name }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];


      // ── Material types ─────────────────────────────────────────────────────────
    window.ChemicalMaterialType = [
        @foreach($ChemicalMaterialTypes as $ChemicalMaterialType)
            {
                id:   {{ $ChemicalMaterialType->id }},
                name: "{{ $ChemicalMaterialType->name }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];



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