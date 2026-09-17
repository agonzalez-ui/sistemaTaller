@extends('layouts.base')

@section('system-header')
    <header class="bg-[#1E3A5F] font-sans text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" aria-label="SIMRH: ir al inicio" class="shrink-0 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                <img src="{{ asset('img/logo.png') }}" alt="SIMRH" class="h-14 w-40 object-contain sm:h-16 sm:w-48">
            </a>
            <div class="flex min-w-0 items-center gap-3">
                <span class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 font-bold text-amber-300 sm:flex" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <div class="hidden min-w-0 text-right sm:block sm:text-left">
                    <p class="text-xs text-slate-300">{{ auth()->user()->role?->name ?? 'Sin rol asignado' }}</p>
                    <p class="max-w-40 truncate text-sm font-semibold sm:max-w-64">{{ auth()->user()->name }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-white/25 px-3 py-3 text-xs font-semibold text-white transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[#1E3A5F] sm:px-4 sm:text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9" />
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>
@endsection

@section('contents')
    <div class="flex min-h-[calc(100dvh-88px)] flex-col bg-slate-100 font-sans">
        <nav aria-label="Navegación principal" class="sticky top-0 z-20 border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-3 py-3 sm:px-6 lg:px-8">
                @php
                    $navigation = [
                        ['Inicio', 'dashboard', 'dashboard', 'home'],
                        ['Clientes', 'customers.index', 'customers.*', 'customers'],
                        ['Motos', 'vehicles.index', 'vehicles.*', 'vehicles'],
                        ['Repuestos', 'spareparts.index', 'spareparts.*', 'parts'],
                        ['Órdenes', 'orders.index', 'orders.*', 'orders'],
                        ['Facturas', 'invoice.index', 'invoice.*', 'invoices'],
                    ];
                @endphp
                @php
                    $permissionModules = ['dashboard' => null, 'customers.index' => 'customers', 'vehicles.index' => 'vehicles', 'spareparts.index' => 'inventory', 'orders.index' => 'orders', 'invoice.index' => 'billing'];
                    $navigation = array_filter($navigation, fn ($item) => $item[1] === 'dashboard' || auth()->user()->hasModulePermission($permissionModules[$item[1]]));
                    if (auth()->user()->isAdministrator()) {
                        $navigation[] = ['Usuarios', 'users.index', 'users.*', 'customers'];
                        $navigation[] = ['Roles', 'roles.index', 'roles.*', 'security'];
                    }
                @endphp
                @php
                    $currentModule = collect($navigation)->first(fn ($item) => request()->routeIs($item[2]));
                @endphp
                <details data-mobile-navigation class="group lg:hidden">
                    <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 rounded-xl px-3 py-2 text-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 [&::-webkit-details-marker]:hidden">
                        <span class="flex items-center gap-3 font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-6 w-6 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
                            Menú
                        </span>
                        <span class="max-w-40 truncate rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">{{ $currentModule[0] ?? 'Panel del taller' }}</span>
                    </summary>
                    <div class="mt-3 max-h-[60dvh] overflow-y-auto border-t border-slate-100 pt-3">
                        <ul class="grid gap-2 sm:grid-cols-2">
                            <x-navigation-links :navigation="$navigation" />
                        </ul>
                    </div>
                </details>
                <ul class="hidden gap-2 lg:grid lg:grid-cols-8">
                    <x-navigation-links :navigation="$navigation" :desktop="true" />
                </ul>
            </div>
        </nav>

        <div class="flex-1 bg-cover bg-center bg-no-repeat px-4 py-6 sm:px-6 lg:px-8 lg:py-8"
             style="background-image: linear-gradient(rgba(241,245,249,.88), rgba(241,245,249,.94)), url('{{ asset('img/fondo-auth.png') }}')">
            <main class="mx-auto w-full @yield('content-width', 'max-w-7xl') rounded-2xl border border-white bg-white p-5 shadow-sm sm:p-7 lg:p-8">
                @hasSection('page-heading')
                    @yield('page-heading')
                @else
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">@yield('title')</h1>
                @endif
                @yield('app-contents')
            </main>
        </div>
        <x-system-footer />
    </div>
@endsection
