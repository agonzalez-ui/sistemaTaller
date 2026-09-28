@extends('layouts.app')
@section('title', 'Orden '.$order->number)
@section('app-contents')
@if(session('success')) <x-alert :message="session('success')" /> @endif
<div class="mt-6 space-y-7">
    @if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>@endif
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('orders.index') }}" class="inline-flex min-h-12 items-center text-slate-700">← Volver a órdenes</a>
        <div class="flex flex-wrap gap-2">
            @if($order->invoices->isNotEmpty())
                @can('module-access',['billing','view'])<a href="{{ route('invoices.show',$order->invoices->first()) }}" class="inline-flex min-h-12 items-center rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white">Ver factura</a>@endcan
            @elseif($order->status->allows_invoicing)
                @can('module-access',['billing','create'])<a href="{{ route('invoices.create',['order'=>$order->id]) }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-400 px-4 py-3 font-bold">Generar factura</a>@endcan
            @endif
            @can('module-access',['orders','edit'])
                @if($order->status->name === 'Listo' && $order->invoices->contains('status', 'ISSUED'))
                    <form method="POST" action="{{ route('orders.deliver', $order) }}" onsubmit="return confirm('¿Confirma que la moto fue entregada al cliente?')">@csrf<button class="min-h-12 rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-500">Registrar entrega</button></form>
                @endif
            @endcan
            @can('module-access',['orders','edit']) @unless($order->status->is_final || $order->invoices->isNotEmpty())<a href="{{ route('orders.edit',$order) }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-50 px-4 py-3 font-semibold text-amber-800">Editar orden</a>@endunless @endcan
            @can('module-access',['orders','delete']) @unless($order->status->is_final || $order->invoices->isNotEmpty())<form method="POST" action="{{ route('orders.destroy',$order) }}" onsubmit="return confirm('¿Cancelar esta orden? Los repuestos se devolverán al inventario.')">@csrf @method('DELETE')<button class="min-h-12 rounded-xl px-4 py-3 font-semibold text-red-700">Cancelar orden</button></form>@endunless @endcan
        </div>
    </div>
    @php
        $workflow = ['Recibido', 'En diagnóstico', 'En reparación', 'Esperando repuesto', 'Listo', 'Entregado'];
        $currentStep = array_search($order->status->name, $workflow, true);
    @endphp
    @if($order->status->name === 'Cancelada')
        <section class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">
            <p class="font-bold">Orden cancelada</p>
            <p class="mt-1 text-sm">El historial y los repuestos utilizados se conservaron; las existencias fueron devueltas al inventario.</p>
        </section>
    @else
        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5" aria-labelledby="workflow-title">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Seguimiento</p><h2 id="workflow-title" class="mt-1 text-lg font-bold text-slate-900">Avance de la orden</h2></div>
                <p class="text-sm font-semibold text-amber-800">Estado actual: {{ $order->status->name }}</p>
            </div>
            <ol class="mt-5 grid gap-2 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($workflow as $index => $step)
                    @php($completed = $currentStep !== false && $index <= $currentStep)
                    <li class="rounded-xl border p-3 text-sm {{ $step === $order->status->name ? 'border-amber-400 bg-amber-100 text-slate-950' : ($completed ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-slate-200 bg-white text-slate-500') }}">
                        <span class="block text-xs font-bold">{{ $completed ? '✓' : $index + 1 }}</span>
                        <span class="mt-1 block font-semibold">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
            <p class="mt-3 text-xs text-slate-500">“Esperando repuesto” puede utilizarse cuando la reparación debe pausarse; luego la orden puede volver a reparación.</p>
        </section>
    @endif
    <section class="grid gap-5 lg:grid-cols-[1.3fr_.7fr]">
        <div class="rounded-2xl bg-slate-900 p-5 text-white sm:p-6"><div class="flex flex-wrap items-center justify-between gap-3"><span class="font-mono text-sm text-amber-300">{{ $order->number }}</span><span class="rounded-full bg-white/10 px-3 py-1 text-sm font-semibold">{{ $order->status->name }}</span></div><h2 class="mt-4 text-2xl font-bold">{{ $order->customer->name }}</h2><p class="mt-2 text-slate-200">{{ $order->vehicle->license_plate }} · {{ $order->vehicle->brand->name }} {{ $order->vehicle->model }} · {{ $order->vehicle->year }}</p><dl class="mt-6 grid gap-4 sm:grid-cols-2"><div><dt class="text-sm text-slate-400">Mecánico</dt><dd class="mt-1 font-semibold">{{ $order->mechanic?->name ?? 'Sin asignar' }}</dd></div><div><dt class="text-sm text-slate-400">Recibida</dt><dd class="mt-1 font-semibold">{{ $order->received_at->format('d/m/Y H:i') }}</dd></div>@if($order->delivered_at)<div><dt class="text-sm text-slate-400">Finalizada</dt><dd class="mt-1 font-semibold">{{ $order->delivered_at->format('d/m/Y H:i') }}</dd></div>@endif</dl></div>
        <div class="rounded-2xl border border-slate-200 p-5"><h2 class="text-lg font-bold">Resumen</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-3"><dt>Repuestos</dt><dd class="font-semibold">₡{{ number_format($partsTotal,2,',','.') }}</dd></div><div class="flex justify-between gap-3"><dt>Mano de obra</dt><dd class="font-semibold">₡{{ number_format((float)$order->labor_cost,2,',','.') }}</dd></div><div class="flex justify-between gap-3 border-t pt-3 text-lg"><dt class="font-bold">Total actual</dt><dd class="font-bold">₡{{ number_format($total,2,',','.') }}</dd></div></dl><p class="mt-4 text-xs text-slate-500">El total es informativo hasta generar la factura.</p></div>
    </section>
    <section class="grid gap-5 lg:grid-cols-2"><div class="rounded-2xl border border-slate-200 p-5"><h2 class="font-bold">Trabajo solicitado</h2><p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $order->description }}</p></div><div class="rounded-2xl border border-slate-200 p-5"><h2 class="font-bold">Diagnóstico y trabajo realizado</h2><p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $order->diagnosis ?: 'Todavía no se ha registrado el diagnóstico.' }}</p></div></section>
    <section><div class="flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-xl font-bold">Repuestos utilizados</h2><p class="mt-1 text-sm text-slate-500">Las existencias se descuentan al agregar y se devuelven al reducir o retirar.</p></div></div>
        @can('module-access',['orders','edit']) @unless($order->status->is_final || $order->invoices->isNotEmpty())
        <form action="{{ route('orders.items.store',$order) }}" method="POST" class="my-5 grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-[1fr_140px_auto]">@csrf <div><label for="spare_part_id" class="block text-sm font-semibold">Repuesto *</label><select name="spare_part_id" id="spare_part_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3"><option value="">Seleccione un repuesto con existencias</option>@foreach($parts as $part)<option value="{{ $part->id }}" @selected(old('spare_part_id') == $part->id)>{{ $part->code }} · {{ $part->name }} · {{ $part->stock_quantity }} disponibles</option>@endforeach</select></div><div><label for="quantity" class="block text-sm font-semibold">Cantidad *</label><input type="number" name="quantity" id="quantity" min="1" max="1000000" required value="{{ old('quantity',1) }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3"></div><button class="min-h-12 self-end rounded-xl bg-amber-400 px-5 py-3 font-semibold">Agregar</button></form>
        @endunless @endcan
        <div class="grid gap-3">@forelse($order->items as $item)<article class="grid min-w-0 gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-[1fr_auto] sm:items-center"><div><h3 class="break-words font-bold">{{ $item->sparePart->name }}</h3><p class="mt-1 text-sm text-slate-500">{{ $item->sparePart->code }} · ₡{{ number_format((float)$item->unit_price,2,',','.') }} c/u · Total ₡{{ number_format((float)$item->line_total,2,',','.') }}</p></div>@can('module-access',['orders','edit']) @unless($order->status->is_final || $order->invoices->isNotEmpty())<div class="flex flex-wrap gap-2"><form method="POST" action="{{ route('orders.items.update',[$order,$item]) }}" class="flex gap-2">@csrf @method('PUT')<input type="hidden" name="spare_part_id" value="{{ $item->spare_part_id }}"><label class="sr-only" for="quantity-{{ $item->id }}">Cantidad</label><input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="1000000" value="{{ $item->quantity }}" class="min-h-12 w-20 rounded-xl border border-slate-300 px-3"><button class="min-h-12 rounded-xl bg-slate-100 px-3 font-semibold">Actualizar</button></form><form method="POST" action="{{ route('orders.items.destroy',[$order,$item]) }}" onsubmit="return confirm('¿Retirar el repuesto y devolverlo al inventario?')">@csrf @method('DELETE')<button class="min-h-12 rounded-xl px-3 text-red-700">Retirar</button></form></div>@endunless @endcan</article>@empty<p class="rounded-xl bg-slate-50 p-6 text-slate-500">Todavía no se han agregado repuestos.</p>@endforelse</div>
    </section>
    <section><h2 class="text-xl font-bold">Historial de estados</h2><div class="mt-4 space-y-3">@foreach($order->histories->sortByDesc('date') as $history)<article class="rounded-xl border border-slate-200 p-4"><div class="flex flex-wrap justify-between gap-2"><span class="font-bold">{{ $history->status->name }}</span><time class="text-xs text-slate-500">{{ $history->date->format('d/m/Y H:i') }}</time></div><p class="mt-2 text-sm text-slate-700">{{ $history->notes ?: 'Sin observaciones.' }}</p><p class="mt-2 text-xs text-slate-500">{{ $history->user->name }}</p></article>@endforeach</div></section>
</div>
@endsection
