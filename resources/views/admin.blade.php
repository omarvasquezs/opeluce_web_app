<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Opeluce | Sistema de Gestion</title>
        <link rel="icon" type="image/png" href="/images/icon.png">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">

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
