@extends('layouts.app')
@section('title', 'Acerca del taller')
@section('content-width', 'max-w-6xl')
@section('app-contents')
<div class="mt-6 space-y-6">
    <section class="relative overflow-hidden rounded-3xl bg-slate-900 p-6 text-white sm:p-9">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-400/10"></div>
        <div class="relative grid items-center gap-8 md:grid-cols-[1fr_auto]">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-amber-300">Taller de motocicletas</p>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">Comprometidos con el cuidado de su motocicleta</h1>
                <p class="mt-4 text-base leading-7 text-slate-300 sm:text-lg">Somos un taller familiar dedicado al mantenimiento y reparación de motocicletas. Trabajamos para ofrecer una atención ordenada, responsable y transparente, conservando un registro claro de cada moto y de los trabajos realizados.</p>
            </div>
            <img src="{{ asset('img/logo.png') }}" alt="Logotipo del taller SIMRH" class="mx-auto h-28 w-72 object-contain md:h-36 md:w-80">
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-3" aria-label="Principios del taller">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700" aria-hidden="true"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></span>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Trabajo responsable</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Cada orden conserva el diagnóstico, los repuestos utilizados y el avance de la reparación.</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700" aria-hidden="true"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a6 6 0 1 0 6 6m-6-6a6 6 0 0 1 6 6m0 0h2m-2 0a6 6 0 0 1-6 6m0 0v2m0-2a6 6 0 0 1-6-6m0 0H4" /></svg></span>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Atención organizada</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Registramos la recepción y condición de la motocicleta para dar seguimiento desde su ingreso hasta la entrega.</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700" aria-hidden="true"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM5 21a7 7 0 0 1 14 0" /></svg></span>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Trato cercano</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Buscamos que cada cliente reciba información clara sobre su motocicleta y el servicio efectuado.</p>
        </article>
    </section>

    <section class="grid gap-6 rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-8 lg:grid-cols-2">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-amber-700">Lo que hacemos</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Servicios del taller</h2>
            <ul class="mt-5 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                @foreach(['Recepción e inspección de motocicletas', 'Diagnóstico mecánico', 'Mantenimiento y reparación', 'Instalación y control de repuestos', 'Seguimiento de órdenes de trabajo', 'Facturación de los servicios'] as $service)
                    <li class="flex gap-3 rounded-xl bg-white p-3"><span class="font-bold text-amber-600" aria-hidden="true">✓</span><span>{{ $service }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-2xl bg-slate-900 p-6 text-white">
            <p class="text-xs font-bold uppercase tracking-widest text-amber-300">Nuestro propósito</p>
            <h2 class="mt-2 text-2xl font-bold">Confianza en cada reparación</h2>
            <p class="mt-4 leading-7 text-slate-300">Nuestro propósito es cuidar la motocicleta del cliente y mantener evidencia organizada del servicio, desde las condiciones en que fue recibida hasta los trabajos y repuestos aplicados.</p>
            <p class="mt-5 border-t border-white/10 pt-5 text-sm text-slate-400">Propietario del taller: <span class="font-semibold text-white">{{ $information['owner'] }}</span></p>
        </div>
    </section>

    <section class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-bold text-slate-900">Gestión respaldada por {{ $information['acronym'] }}</h2>
            <p class="mt-1 text-sm text-slate-600">El sistema ayuda al personal autorizado a administrar clientes, motocicletas, órdenes, inventario y facturación.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 items-center rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">Volver al inicio</a>
            @can('module-access', ['help','view'])
                <a href="{{ route('help') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-100 px-5 py-3 font-semibold text-amber-800">Consultar ayuda →</a>
            @endcan
        </div>
    </section>
</div>
@endsection
