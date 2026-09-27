@extends('layouts.app')
@section('title', 'Facturas')
@section('app-contents')
@if(session('success'))<div class="mt-5"><x-alert :message="session('success')" /></div>@endif
<div class="mt-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="text-xl font-bold">Comprobantes emitidos</h2><p class="mt-1 text-sm text-slate-500">Consulte, imprima o anule las facturas del taller.</p></div>@can('module-access',['billing','create'])<a href="{{ route('invoices.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-400 px-5 py-3 font-bold">+ Nueva factura</a>@endcan</div>
    <form method="GET" class="grid gap-3 rounded-2xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_180px_160px_160px_auto]">
        <div><label for="search" class="block text-sm font-semibold">Buscar</label><input id="search" name="search" value="{{ request('search') }}" placeholder="Factura, orden, cliente o placa" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3"></div>
        <div><label for="status" class="block text-sm font-semibold">Estado</label><select id="status" name="status" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3"><option value="">Todos</option><option value="ISSUED" @selected(request('status')==='ISSUED')>Emitida</option><option value="CANCELLED" @selected(request('status')==='CANCELLED')>Anulada</option></select></div>
        <div><label for="from" class="block text-sm font-semibold">Desde</label><input id="from" name="from" type="date" value="{{ request('from') }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3"></div>
        <div><label for="to" class="block text-sm font-semibold">Hasta</label><input id="to" name="to" type="date" value="{{ request('to') }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3"></div>
        <button class="min-h-12 self-end rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">Filtrar</button>
    </form>
    <div class="grid gap-4">@forelse($invoices as $invoice)
        <a href="{{ route('invoices.show',$invoice) }}" class="grid gap-4 rounded-2xl border border-slate-200 p-5 transition hover:border-amber-400 sm:grid-cols-[1fr_auto] sm:items-center">
            <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-mono font-bold text-slate-900">{{ $invoice->number }}</h3><span @class(['rounded-full px-2.5 py-1 text-xs font-bold','bg-emerald-50 text-emerald-700'=>$invoice->status==='ISSUED','bg-red-50 text-red-700'=>$invoice->status==='CANCELLED'])>{{ $invoice->status==='ISSUED' ? 'Emitida' : 'Anulada' }}</span></div><p class="mt-2 break-words font-semibold">{{ $invoice->customer->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $invoice->vehicle->license_plate }} · Orden {{ $invoice->order->number }} · {{ $invoice->date->format('d/m/Y H:i') }}</p></div>
            <div class="text-left sm:text-right"><p class="text-xs uppercase tracking-wide text-slate-500">Total</p><p class="mt-1 text-xl font-bold">₡{{ number_format((float)$invoice->total,2,',','.') }}</p><span class="mt-2 inline-block font-semibold text-amber-700">Ver factura →</span></div>
        </a>
    @empty <p class="rounded-2xl bg-slate-50 p-8 text-center text-slate-500">No hay facturas que coincidan con la consulta.</p>@endforelse</div>
    {{ $invoices->links() }}
</div>
@endsection
