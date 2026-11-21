<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'SIMRH') }} - @yield('title')</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">


    @fonts
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body>

    @hasSection('system-header')
        @yield('system-header')
    @else
    <header class="bg-[#1E3A5F] py-5">
        <div class="mx-auto flex max-w-6xl items-center">
            <div class="w-full max-w-100">
                <img src="{{ asset('img/logo.png') }}" alt="SIMRH Logo" class="w-full block">
            </div>
        </div>
    </header>
    @endif

    @yield('contents')
</body>

</html>


