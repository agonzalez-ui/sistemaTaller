<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') · @yield('title') · {{ config('app.name', 'SIMRH') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[#DCE3EA] font-sans text-slate-900">
    <header class="border-b border-white/10 bg-[#172F4F] px-4 py-3 shadow-md sm:px-6">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <a href="{{ url('/') }}" aria-label="SIMRH: volver al inicio" class="rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                <img src="{{ asset('img/logo.png') }}" alt="SIMRH" class="h-14 w-40 object-contain sm:h-16 sm:w-48">
            </a>
            <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-slate-200">Taller de motocicletas</span>
        </div>
    </header>

    <main class="flex min-h-[calc(100vh-89px)] items-center justify-center bg-cover bg-center px-4 py-10 sm:px-6"
          style="background-image: linear-gradient(rgba(220,227,234,.92), rgba(220,227,234,.97)), url('{{ asset('img/fondo-auth.png') }}')">
        <section class="relative w-full max-w-3xl overflow-hidden rounded-3xl border border-slate-300/80 bg-[#F7F8FA]/95 p-6 shadow-xl sm:p-10 lg:p-12">
            <div class="pointer-events-none absolute -right-8 -top-16 select-none text-[11rem] font-black leading-none text-slate-200/70 sm:text-[15rem]" aria-hidden="true">@yield('code')</div>
            <div class="relative max-w-xl">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl @yield('icon-colors', 'bg-amber-100 text-amber-700')" style="width:4rem;height:4rem">
                    @yield('icon')
                </span>
                <p class="mt-7 text-sm font-black uppercase tracking-[.2em] text-amber-700">Error @yield('code')</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">@yield('title')</h1>
                <p class="mt-4 text-base leading-7 text-slate-600 sm:text-lg">@yield('message')</p>
                <p class="mt-3 text-sm leading-6 text-slate-500">@yield('suggestion')</p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ url('/') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-amber-400 px-6 py-3 font-bold text-slate-950 transition hover:bg-amber-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Ir al inicio</a>
                    @hasSection('secondary-action')
                        @yield('secondary-action')
                    @endif
                </div>
            </div>
        </section>
    </main>
</body>
</html>
