<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gestion Courier') }}</title>

    <!-- Scripts / Styles (Bootstrap via Vite, comme le reste du projet) -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="py-4">
        @yield('content')
    </main>
</body>
</html>
