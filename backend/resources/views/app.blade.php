<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Pathfinder') }}</title>

        <!-- Favicon - 3x zoomed for maximum size and visibility -->
        <link rel="icon" type="image/x-icon" href="/favicon.ico?v=4">
        <link rel="icon" type="image/png" sizes="48x48" href="/favicon-48x48.png?v=4">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=4">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=4">
        <link rel="shortcut icon" href="/favicon.ico?v=4">
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon-48x48.png?v=4">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
