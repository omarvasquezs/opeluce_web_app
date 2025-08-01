<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Opeluce | Sistema de Gestion</title>
        <link rel="icon" type="image/png" href="/images/icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div id="app">
            <!--
            <div class="text-center p-4">
                <h1>Loading Admin System...123</h1>
                <p>If this message persists, check the browser console or ensure the Vite dev server is running.</p>
            </div>
            -->
        </div>
    </body>
</html>
