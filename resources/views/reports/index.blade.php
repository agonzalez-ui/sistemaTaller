@extends('layouts.app')
@section('title', 'Reportes')
@section('app-contents')
<div class="mt-6">
    <p class="max-w-3xl text-slate-600">Consulte información operativa y bitácoras de seguridad. Cada reporte permite aplicar filtros, revisar totales e imprimir el resultado.</p>
    <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @php($reports = [
            ['Facturación','reports.billing','invoices','Ventas emitidas, anulaciones y total del periodo.'],
            ['Órdenes de trabajo','reports.orders','orders','Trabajos por estado, mecánico y fecha.'],
            ['Inventario','reports.inventory','parts','Existencias, mínimos y valor de los repuestos.'],
            ['Ingresos y salidas','reports.access','security','Sesiones de los usuarios por fecha.'],
            ['Movimientos de usuarios','reports.activity','reports','Cambios registrados en la bitácora de auditoría.'],
        ])
        @foreach($reports as [$label,$route,$icon,$description])
            <a href="{{ route($route) }}" class="group flex min-h-52 flex-col rounded-2xl border border-slate-200 p-5 transition hover:border-amber-400 hover:bg-amber-50/40 sm:p-6">
                <div class="flex items-center justify-between"><span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><x-module-icon :name="$icon" /></span><span class="text-xl text-slate-400 group-hover:text-amber-700">↗</span></div>
                <h2 class="mt-5 text-lg font-bold">{{ $label }}</h2><p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p><span class="mt-auto pt-5 font-semibold text-amber-700">Abrir reporte →</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
