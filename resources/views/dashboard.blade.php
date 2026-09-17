@extends('layouts.app')

@section('title', 'Panel del taller')

@section('page-heading')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
        <div>
            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-amber-700">Administración SIMRH</p>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Panel del taller</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Clientes, motos y trabajo del taller en un solo lugar.</p>
        </div>
        @can('module-access', ['customers', 'create'])
        <a href="{{ route('customers.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 self-start rounded-xl bg-amber-400 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
            <span aria-hidden="true" class="text-xl">+</span> Nuevo cliente
        </a>
        @endcan
    </div>
@endsection

@section('app-contents')
    @if (session('success'))
        <div class="mt-5"><x-alert :message="session('success')" /></div>
    @endif

    @if (! auth()->user()->role_id)
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-900">
            Su cuenta está pendiente de asignación de rol. El administrador debe asignarle un rol para acceder a los módulos del taller.
        </div>
    @endif
    <div class="mt-7 grid gap-5 lg:grid-cols-3">
        @can('module-access', ['orders', 'view'])
        <a href="{{ route('orders.index') }}" class="group relative flex flex-col justify-between gap-8 overflow-hidden rounded-2xl bg-[#1E3A5F] p-6 text-white transition hover:bg-[#254870] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 sm:p-7">
            <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full border-[24px] border-white/5" aria-hidden="true"></div>
            <div class="relative">
                <span class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-400 text-slate-950"><x-module-icon name="orders" class="h-7 w-7" /></span>
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-300">Trabajo del taller</p>
                <h2 class="text-2xl font-bold">Órdenes de trabajo</h2>
                <p class="mt-3 max-w-sm text-sm leading-6 text-slate-200">Consulte las órdenes y dé seguimiento a las reparaciones.</p>
            </div>
            <span class="relative flex min-h-12 items-center justify-between border-t border-white/20 pt-4 font-semibold">Ver órdenes <span aria-hidden="true" class="text-xl">→</span></span>
        </a>

        @endcan
        <div @class(['grid gap-4 sm:grid-cols-2', 'lg:col-span-2' => auth()->user()->hasModulePermission('orders'), 'lg:col-span-3' => ! auth()->user()->hasModulePermission('orders')])>
            @php
                $modules = [
                    ['Clientes', 'customers.index', 'customers', 'Información de contacto y motos de sus clientes.'],
                    ['Motos', 'vehicles.index', 'vehicles', 'Consulte las motos registradas en el taller.'],
                    ['Repuestos', 'spareparts.index', 'parts', 'Catálogo de piezas y disponibilidad de inventario.'],
                    ['Facturas', 'invoice.index', 'invoices', 'Consulte los comprobantes de los trabajos realizados.'],
                ];
            @endphp
            @foreach ($modules as [$label, $route, $icon, $description])
                @php($modulePermission = ['customers.index' => 'customers', 'vehicles.index' => 'vehicles', 'spareparts.index' => 'inventory', 'invoice.index' => 'billing'][$route])
                @can('module-access', [$modulePermission, 'view'])
                <a href="{{ route($route) }}" class="group flex min-h-44 flex-col rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-amber-400 hover:bg-amber-50/40 hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><x-module-icon :name="$icon" /></span>
                        <span aria-hidden="true" class="text-xl text-slate-400 transition group-hover:text-amber-700">↗</span>
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $label }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p>
                </a>
                @endcan
            @endforeach
        </div>
    </div>
    @can('manage-security')
        <div class="mt-7 border-t border-slate-200 pt-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Administración y seguridad</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('users.index') }}" class="flex min-h-16 items-center gap-4 rounded-xl bg-slate-50 p-4 font-semibold text-slate-800 hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"><x-module-icon name="customers" /> Usuarios <span class="ml-auto" aria-hidden="true">→</span></a>
                <a href="{{ route('roles.index') }}" class="flex min-h-16 items-center gap-4 rounded-xl bg-slate-50 p-4 font-semibold text-slate-800 hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"><x-module-icon name="security" /> Roles y permisos <span class="ml-auto" aria-hidden="true">→</span></a>
            </div>
        </div>
    @endcan
@endsection