@extends('layouts.app')
@section('title', 'Acerca de SIMRH')
@section('content-width', 'max-w-5xl')
@section('app-contents')
<div class="mt-6 space-y-6">
    <section class="rounded-2xl bg-slate-900 p-6 text-white sm:p-8">
        <span class="inline-flex rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-slate-950">{{ $information['status'] }}</span>
        <h2 class="mt-4 text-2xl font-bold">{{ $information['acronym'] }}</h2>
        <p class="mt-2 text-lg text-slate-200">{{ $information['name'] }}</p>
        <p class="mt-4 text-sm leading-relaxed text-slate-300">{{ $information['purpose'] }}</p>
    </section>
    <section aria-labelledby="system-information">
        <h2 id="system-information" class="mb-4 text-lg font-bold text-slate-900">Información del sistema</h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            @foreach(['acronym'=>'Acrónimo','name'=>'Nombre del sistema','version'=>'Versión','version_date'=>'Fecha de versión','owner'=>'Propietario','developer'=>'Desarrollador'] as $key=>$label)
                <div class="min-w-0 rounded-xl border border-slate-200 p-4">
                    <dt class="text-sm text-slate-500">{{ $label }}</dt>
                    <dd class="mt-2 break-words font-semibold text-slate-900">{{ $key === 'version_date' ? Illuminate\Support\Carbon::parse($information[$key])->format('d/m/Y') : $information[$key] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
    <section class="rounded-2xl bg-slate-50 p-5">
        <h2 class="text-lg font-bold text-slate-900">Tecnología utilizada</h2>
        <dl class="mt-4 grid gap-4 sm:grid-cols-3">
            @foreach(['Lenguaje'=>'PHP','Framework'=>'Laravel · MVC','Base de datos'=>'MySQL'] as $label=>$value)
                <div><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-slate-900">{{ $value }}</dd></div>
            @endforeach
        </dl>
    </section>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('dashboard') }}" class="inline-flex min-h-12 items-center rounded-xl bg-slate-800 px-5 py-3 font-semibold text-white">Volver al inicio</a>
        @can('module-access', ['help','view'])
            <a href="{{ route('help') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-50 px-5 py-3 font-semibold text-amber-800">Consultar ayuda →</a>
        @endcan
    </div>
</div>
@endsection