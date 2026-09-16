<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'SIMRH') }} - @yield('title')</title>

    @fonts
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body>

    <header class="bg-[#1E3A5F] py-5">
        <div class="max-w-6xl mx-auto flex flex-col lg:flex-row items-center lg:justify-between">
            <div class="w-full max-w-100">
                <img src="{{ asset('img/logo.png') }}" alt="SIMRH Logo" class="w-full block">
            </div>

            <nav class="flex flex-col lg:flex-row items-center gap-4">
                @auth
                    <p class="text-white text-xl">Bienvenido {{ auth()->user()->name }}</p>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="text-white font-bold uppercase p-2">Iniciar Sesión</a>
                        <a href="{{ route('register') }}"
                            class="font-bold uppercase border-2 border-amber-500 px-5 py-2 text-amber-500">Crear Cuenta</a>
                    @endif

                @endauth

            </nav>

    </header>

    @yield('contents')
</body>

</html>
