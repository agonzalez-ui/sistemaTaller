@extends('layouts.auth')

@section('title')
    Administración SIMRH
@endsection

@section('auth-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

    <div class="mt-3">
        <p class="text-sm leading-6 text-slate-500">
            Seleccione el módulo que desea administrar.
        </p>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Clientes --}}
            <a href="{{ route('customers.index') }}"
                class="group flex h-full flex-col rounded-xl border border-slate-200
                      bg-white p-5 shadow-sm transition duration-200
                      hover:-translate-y-1 hover:border-amber-400 hover:shadow-md
                      focus-visible:outline-none focus-visible:ring-2
                      focus-visible:ring-amber-500 focus-visible:ring-offset-2">

                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-xl bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                </div>

                <h2 class="text-lg font-semibold text-slate-900">
                    Clientes
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Consulte y administre la información de sus clientes.
                </p>

                <div class="mt-auto pt-5">
                    <span
                        class="flex items-center justify-between gap-2
                                 whitespace-nowrap text-sm font-semibold text-amber-700">
                        Gestionar clientes
                        <span class="shrink-0 transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </span>
                </div>
            </a>

            {{-- Motos --}}
            <a href="{{ route('vehicles.index') }}"
                class="group flex h-full flex-col rounded-xl border border-slate-200
                      bg-white p-5 shadow-sm transition duration-200
                      hover:-translate-y-1 hover:border-amber-400 hover:shadow-md
                      focus-visible:outline-none focus-visible:ring-2
                      focus-visible:ring-amber-500 focus-visible:ring-offset-2">

                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-xl bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <circle cx="5" cy="16" r="3" />
                        <circle cx="19" cy="16" r="3" />
                        <path d="M7.5 14h5l4-4H6l1.5 4 4-4" />
                        <path d="M13 6h2l1.5 3 2.5 7" />
                    </svg>
                </div>

                <h2 class="text-lg font-semibold text-slate-900">
                    Motos
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Consulte y administre las motos registradas.
                </p>

                <div class="mt-auto pt-5">
                    <span
                        class="flex items-center justify-between gap-2
                                 whitespace-nowrap text-sm font-semibold text-amber-700">
                        Gestionar motos
                        <span class="shrink-0 transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </span>
                </div>
            </a>

            {{-- Repuestos --}}
            <a href="{{ route('spareparts.index') }}"
                class="group flex h-full flex-col rounded-xl border border-slate-200
                      bg-white p-5 shadow-sm transition duration-200
                      hover:-translate-y-1 hover:border-amber-400 hover:shadow-md
                      focus-visible:outline-none focus-visible:ring-2
                      focus-visible:ring-amber-500 focus-visible:ring-offset-2">

                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-xl bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path
                            d="M9 3h6l.5 2.5 2 1.2 2.4-.8 3 5.2-2 1.6v2.6l2 1.6-3 5.2-2.4-.8-2 1.2L15 24H9l-.5-2.5-2-1.2-2.4.8-3-5.2 2-1.6v-2.6l-2-1.6 3-5.2 2.4.8 2-1.2z"
                            transform="translate(2.4 1.2) scale(.8)" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </div>

                <h2 class="text-lg font-semibold text-slate-900">
                    Repuestos
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Consulte y administre el catálogo de repuestos.
                </p>

                <div class="mt-auto pt-5">
                    <span
                        class="flex items-center justify-between gap-2
                                 whitespace-nowrap text-sm font-semibold text-amber-700">
                        Gestionar repuestos
                        <span class="shrink-0 transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </span>
                </div>
            </a>

            {{-- Factura --}}
            <a href="{{ route('invoice.index') }}"
                class="group flex h-full flex-col rounded-xl border border-slate-200
                      bg-white p-5 shadow-sm transition duration-200
                      hover:-translate-y-1 hover:border-amber-400 hover:shadow-md
                      focus-visible:outline-none focus-visible:ring-2
                      focus-visible:ring-amber-500 focus-visible:ring-offset-2">

                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-xl bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M5 3h14v18l-2-1.5-2 1.5-3-1.5L9 21l-2-1.5L5 21V3z" />
                        <path d="M9 7h6" />
                        <path d="M9 11h6" />
                        <path d="M9 15h2" />
                        <path d="M14 15h1" />
                    </svg>
                </div>

                <h2 class="text-lg font-semibold text-slate-900">
                    Facturas
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Consulte y administre las facturas.
                </p>

                <div class="mt-auto pt-5">
                    <span
                        class="flex items-center justify-between gap-2
                                 whitespace-nowrap text-sm font-semibold text-amber-700">
                        Gestionar facturas
                        <span class="shrink-0 transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </span>
                </div>
            </a>

            {{-- Órdenes --}}
            <a href="{{ route('orders.index') }}"
                class="group flex h-full flex-col rounded-xl border border-slate-200
                      bg-white p-5 shadow-sm transition duration-200
                      hover:-translate-y-1 hover:border-amber-400 hover:shadow-md
                      focus-visible:outline-none focus-visible:ring-2
                      focus-visible:ring-amber-500 focus-visible:ring-offset-2">

                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center
                            rounded-xl bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <rect x="8" y="2" width="8" height="4" rx="1" />
                        <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2" />
                        <path d="m8 10 1 1 2-2" />
                        <path d="M14 10h2" />
                        <path d="m8 16 1 1 2-2" />
                        <path d="M14 16h2" />
                    </svg>
                </div>

                <h2 class="text-lg font-semibold text-slate-900">
                    Órdenes
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Consulte y administre las órdenes.
                </p>

                <div class="mt-auto pt-5">
                    <span
                        class="flex items-center justify-between gap-2
                                 whitespace-nowrap text-sm font-semibold text-amber-700">
                        Gestionar órdenes
                        <span class="shrink-0 transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                    </span>
                </div>
            </a>

        </div>
    </div>
@endsection
