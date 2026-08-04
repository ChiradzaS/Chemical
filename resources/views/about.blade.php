<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>companyInfo</title>

      <!-- @viteReactRefresh
      @vite(['resources/css/app.css', 'resources/js/about.tsx']) -->

    



<link rel="stylesheet" href="{{ asset('public/build/assets/app.css') }}">
<script src="{{ asset('public/build/assets/about.js ') }}" type="module" ></script> 

</head>
<body>

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