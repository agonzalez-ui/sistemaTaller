@extends('layouts.auth')

@section('title')
    Administración SIMRH
@endsection

@section('auth-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

    <div class="mt-6">
        {{-- customers --}}
        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-3 rounded-xl bg-amber-500
                      px-6 py-3 font-semibold text-slate-950 shadow-md
                      transition duration-200 hover:-translate-y-0.5
                      hover:bg-amber-400 hover:shadow-lg
                      focus-visible:outline-none focus-visible:ring-2
                      focus-visible:ring-amber-500 focus-visible:ring-offset-2">

            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2
                                 M22 21v-2a4 4 0 0 0-3-3.87
                                 M16 3.13a4 4 0 0 1 0 7.75" />
                <circle cx="9" cy="7" r="4" />
            </svg>

            Gestionar clientes
            <span aria-hidden="true">→</span>
        </a>

        {{-- vehicles --}}
        <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-3 rounded-xl bg-amber-500
                      px-6 py-3 font-semibold text-slate-950 shadow-md
                      transition duration-200 hover:-translate-y-0.5
                      hover:bg-amber-400 hover:shadow-lg
                      focus-visible:outline-none focus-visible:ring-2
                      focus-visible:ring-amber-500 focus-visible:ring-offset-2">

            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2 16a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 16a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14h5l4 -4h-10.5m1.5 4l4 -4" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 6h2l1.5 3l2 4" />
            </svg>

            Gestionar vehiculos
            <span aria-hidden="true">→</span>
        </a>
    </div>
@endsection