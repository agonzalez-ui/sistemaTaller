@extends('layouts.app')
@section('title', 'Factura '.$invoice->number)
@section('content-width', 'max-w-5xl')
@section('app-contents')
@if(session('success'))<div class="mt-5 print:hidden"><x-alert :message="session('success')" /></div>@endif
<div class="mt-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3 print:hidden"><a href="{{ route('invoices.index') }}" class="inline-flex min-h-12 items-center text-slate-700">← Volver a facturas</a><div class="flex flex-wrap gap-2"><button type="button" onclick="window.print()" class="min-h-12 rounded-xl bg-slate-100 px-4 py-3 font-semibold">Imprimir</button><a href="{{ route('orders.show',$invoice->order) }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-50 px-4 py-3 font-semibold text-amber-800">Ver orden</a></div></div>
    @if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-800 print:hidden">{{ $errors->first() }}</div>@endif
    <article class="invoice-sheet rounded-2xl border border-slate-200 bg-white p-5 sm:p-8">
        <header class="flex flex-col justify-between gap-6 border-b border-slate-200 pb-6 sm:flex-row sm:items-start"><div class="flex flex-wrap items-center gap-5"><img src="{{ asset('img/logo.png') }}" alt="Logo SIMRH" class="h-20 w-52 object-contain"><div><p class="text-xs font-bold uppercase tracking-[.2em] text-amber-700">Taller de motocicletas</p><h2 class="mt-2 text-3xl font-bold">Factura</h2><p class="mt-2 font-mono text-slate-600">{{ $invoice->number }}</p></div></div><div class="sm:text-right"><span @class(['inline-flex rounded-full px-3 py-1 text-sm font-bold','bg-emerald-50 text-emerald-700'=>$invoice->status==='ISSUED','bg-red-50 text-red-700'=>$invoice->status==='CANCELLED'])>{{ $invoice->status==='ISSUED' ? 'Emitida' : 'Anulada' }}</span><p class="mt-3 text-sm text-slate-600">{{ $invoice->date->format('d/m/Y H:i') }}</p></div></header>
        @if($invoice->status==='CANCELLED')<div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800"><p class="font-bold">Factura anulada el {{ $invoice->cancelled_at->format('d/m/Y H:i') }}</p><p class="mt-1 text-sm">{{ $invoice->cancellation_reason }}</p></div>@endif
        <section class="grid gap-5 border-b border-slate-200 py-6 sm:grid-cols-2"><div><h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Cliente</h3><p class="mt-2 font-bold">{{ $invoice->customer->name }}</p><p class="mt-1 text-sm text-slate-600">Identificación: {{ $invoice->customer->identification_number }}</p><p class="mt-1 text-sm text-slate-600">{{ $invoice->customer->email ?: 'Sin correo registrado' }}</p></div><div><h3 class="text-xs font-bold uppercase tracking-wide text-slate-500">Moto y orden</h3><p class="mt-2 font-bold">{{ $invoice->vehicle->license_plate }} · {{ $invoice->vehicle->brand->name }} {{ $invoice->vehicle->model }}</p><p class="mt-1 text-sm text-slate-600">Orden {{ $invoice->order->number }}</p><p class="mt-1 text-sm text-slate-600">Emitida por {{ $invoice->creator->name }}</p></div></section>
        <section class="py-6"><div class="overflow-x-auto"><table class="w-full min-w-[580px] text-left text-sm"><thead><tr class="border-b text-slate-500"><th class="py-3 pr-3">Detalle</th><th class="px-3 py-3 text-right">Cantidad</th><th class="px-3 py-3 text-right">Precio</th><th class="py-3 pl-3 text-right">Total</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr class="border-b border-slate-100"><td class="py-4 pr-3"><span class="font-semibold">{{ $item->description }}</span><span class="mt-1 block text-xs text-slate-500">{{ $item->line_type==='PART' ? 'Repuesto' : 'Servicio' }}</span></td><td class="px-3 py-4 text-right">{{ $item->quantity }}</td><td class="px-3 py-4 text-right">₡{{ number_format((float)$item->unit_price,2,',','.') }}</td><td class="py-4 pl-3 text-right font-semibold">₡{{ number_format((float)$item->line_total,2,',','.') }}</td></tr>@endforeach</tbody></table></div></section>
        <section class="ml-auto max-w-sm space-y-3 border-t border-slate-200 pt-5 text-sm"><div class="flex justify-between gap-4"><span>Subtotal</span><strong>₡{{ number_format((float)$invoice->subtotal,2,',','.') }}</strong></div><div class="flex justify-between gap-4"><span>Descuento</span><strong>− ₡{{ number_format((float)$invoice->discount,2,',','.') }}</strong></div><div class="flex justify-between gap-4"><span>Impuesto ({{ number_format((float)$invoice->tax_rate,2,',','.') }}%)</span><strong>₡{{ number_format((float)$invoice->tax_amount,2,',','.') }}</strong></div><div class="flex justify-between gap-4 border-t pt-3 text-xl"><span class="font-bold">Total</span><strong>₡{{ number_format((float)$invoice->total,2,',','.') }}</strong></div></section>
        <footer class="mt-8 border-t border-slate-200 pt-4 text-xs leading-5 text-slate-500">Documento generado por SIMRH. Los importes se conservan como fueron emitidos y no cambian aunque se modifique posteriormente el catálogo de repuestos.</footer>
    </article>
    @can('module-access', ['billing', 'delete'])
        @if($invoice->status === 'ISSUED')
            <section class="rounded-2xl border border-red-200 p-5 print:hidden">
                <h2 class="font-bold text-red-800">Anular factura</h2>
                <p class="mt-2 text-sm text-slate-600">La anulación conserva el comprobante y su auditoría. No modifica la orden ni devuelve inventario.</p>
                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="mt-4 flex flex-col gap-3 sm:flex-row" onsubmit="return confirm('¿Anular esta factura? Esta acción quedará registrada.')">
                    @csrf
                    @method('DELETE')
                    <label for="cancellation_reason" class="sr-only">Motivo de anulación</label>
                    <input id="cancellation_reason" name="cancellation_reason" required minlength="5" maxlength="250" placeholder="Indique el motivo de la anulación" class="min-h-12 flex-1 rounded-xl border border-slate-300 px-3">
                    <button class="min-h-12 rounded-xl bg-red-600 px-5 py-3 font-bold text-white">Anular factura</button>
                </form>
            </section>
        @endif
    @endcan
</div>
@endsection
