<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chemical Delivery / Invoice</title>


 
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/chemicaldelivery.tsx']) 

    <!-- <link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
    <script type="module" src="{{ asset('public/build/assets/chemicaldelivery.js') }}"></script> -->

</head>
<body>

 <script>

    // ── Customers ─────────────────────────────────────────────────────────────
    // window.customersData = [
    //     @foreach($customers as $customer)
    //         {
    //             id:   {{ $customer->id }},
    //             name: "{{ addslashes($customer->name) }}"
    //         }{{ !$loop->last ? ',' : '' }}
    //     @endforeach
    // ];


    window.customersData = [
    @foreach($customers as $customer)
    {
        id:               {{ $customer->id }},
        name:             {!! json_encode($customer->name) !!},
        legalName:        {!! json_encode($customer->legalName) !!},
        vatNo:            {!! json_encode($customer->vatNo) !!},
        accountNumber:    {!! json_encode($customer->accountNumber) !!},
        emailAddress:     {!! json_encode($customer->emailAddress) !!},
        phoneNumber:      {!! json_encode($customer->phoneNumber) !!},
        mobileNumber:     {!! json_encode($customer->mobileNumber) !!},
        contactPerson:    {!! json_encode($customer->contactPerson) !!},
        contactPersonLastName: {!! json_encode($customer->contactPersonLastName) !!},
        sAAttentionTo:    {!! json_encode($customer->sAAttentionTo) !!},
        sAAttentionLine1: {!! json_encode($customer->sAAttentionLine1) !!},
        sAAttentionLine2: {!! json_encode($customer->sAAttentionLine2) !!},
        sACity:           {!! json_encode($customer->sACity) !!},
        sARegion:         {!! json_encode($customer->sARegion) !!},
        sAPostalCode:     {!! json_encode($customer->sAPostalCode) !!},
        sACountry:        {!! json_encode($customer->sACountry) !!},
        pOAddressLine1:   {!! json_encode($customer->pOAddressLine1) !!},
        pOAddressLine2:   {!! json_encode($customer->pOAddressLine2) !!},
        pOCity:           {!! json_encode($customer->pOCity) !!},
        pORegion:         {!! json_encode($customer->pORegion) !!},
        pOPostalCode:     {!! json_encode($customer->pOPostalCode) !!},
        pOCountry:        {!! json_encode($customer->pOCountry) !!}
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
];

    // ── Chemical products (with price, VAT, container size) ───────────────────
    window.chemicalProductsData = [
        @foreach($chemicalProducts as $product)
            {
                id:                {{ $product->id }},
                name:              "{{ addslashes($product->name) }}",
                sku:               "{{ addslashes($product->sku ?? '') }}",
                price:             {{ $product->price ?? 0 }},
                vat_applicable:    {{ $product->vat_applicable ? 1 : 0 }},
                vat_rate:          {{ $product->vat_rate ?? 15 }},
                container_size_id: {{ $product->container_size_id ?? 'null' }},
                stock_unit_id:     {{ $product->stock_unit_id ?? 'null' }}
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Container sizes (= unit pack) ─────────────────────────────────────────
    window.containerSizesData = [
        @foreach($containerSizes as $size)
            {
                id:   {{ $size->id }},
                name: "{{ addslashes($size->name) }}"
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ];

    // ── Laravel API base URL ───────────────────────────────────────────────────
    window.laravelApiUrl = "{{ url('') }}";

  </script>

    <div id="root"></div>

    <script>
        setTimeout(() => {
            const rootEl = document.getElementById('root');
            const rootContent = rootEl ? rootEl.innerHTML : '';
            const statusEl = document.getElementById('react-status');

            if (statusEl) {
                statusEl.innerHTML = rootContent.trim() === ''
                    ? 'React NOT loaded ❌'
                    : 'React loaded ✅';
            }
        }, 2000);
    </script>

</body>
</html>