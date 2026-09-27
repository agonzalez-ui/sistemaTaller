@extends('layouts.app')
@section('title', 'Nueva factura')
@section('content-width', 'max-w-5xl')
@section('app-contents')
<div class="mt-6 space-y-6">
    <a href="{{ route('invoices.index') }}" class="inline-flex min-h-12 items-center text-slate-700">← Volver a facturas</a>
    @if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-800"><p class="font-bold">Revise la información:</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($orders->isEmpty())<div class="rounded-2xl bg-slate-50 p-8 text-center"><h2 class="text-xl font-bold">No hay órdenes disponibles</h2><p class="mt-2 text-slate-600">Para facturar, la orden debe estar en estado Lista o Entregada y no tener otra factura.</p><a href="{{ route('orders.index') }}" class="mt-4 inline-flex min-h-12 items-center font-semibold text-amber-700">Revisar órdenes →</a></div>
    @else
    <form method="POST" action="{{ route('invoices.store') }}" class="space-y-6">@csrf
        <section class="rounded-2xl border border-slate-200 p-5 sm:p-6"><label for="order_id" class="block font-bold">Orden a facturar *</label><select id="order_id" name="order_id" required class="mt-3 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3"><option value="">Seleccione una orden lista o entregada</option>@foreach($orders as $order)@php($orderTotal=(float)$order->labor_cost+(float)$order->items->sum('line_total'))<option value="{{ $order->id }}" data-total="{{ number_format($orderTotal,2,'.','') }}" @selected(old('order_id',$selectedOrder?->id)==$order->id)>{{ $order->number }} · {{ $order->customer->name }} · {{ $order->vehicle->license_plate }} · ₡{{ number_format($orderTotal,2,',','.') }}</option>@endforeach</select><p class="mt-2 text-sm text-slate-500">Los repuestos y la mano de obra se copian desde la orden y quedan protegidos como respaldo histórico.</p></section>
        <section class="grid gap-5 rounded-2xl border border-slate-200 p-5 sm:grid-cols-2 sm:p-6"><div><label for="discount" class="block font-bold">Descuento (₡)</label><input id="discount" name="discount" type="number" min="0" step="0.01" value="{{ old('discount','0.00') }}" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3"></div><div><label for="tax_rate" class="block font-bold">Impuesto (%)</label><input id="tax_rate" name="tax_rate" type="number" min="0" max="100" step="0.01" value="{{ old('tax_rate','13.00') }}" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3"><p class="mt-2 text-xs text-slate-500">Puede ajustarlo a 0 u otro porcentaje según corresponda.</p></div></section>
        <div class="flex flex-wrap gap-3"><button class="min-h-12 rounded-xl bg-amber-400 px-6 py-3 font-bold">Generar factura</button><a href="{{ route('invoices.index') }}" class="inline-flex min-h-12 items-center px-3 font-semibold text-slate-600">Cancelar</a></div>
    </form>@endif
</div>
@endsection
