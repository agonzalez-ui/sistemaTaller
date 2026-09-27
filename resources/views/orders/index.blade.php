@extends('layouts.app')
@section('title', 'Órdenes de trabajo')
@section('app-contents')
@if(session('success')) <x-alert :message="session('success')" /> @endif
<div class="mt-6">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-600">Seguimiento del trabajo realizado en cada moto.</p>@can('module-access', ['orders','create'])<a href="{{ route('orders.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950">+ Nueva orden</a>@endcan</div>
    <form method="GET" action="{{ route('orders.index') }}" class="mb-6 grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2"><label for="search" class="block text-sm font-semibold">Buscar</label><input type="search" name="search" id="search" maxlength="100" value="{{ request('search') }}" placeholder="Número, cliente o placa" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4 py-3"></div>
        <div><label for="status" class="block text-sm font-semibold">Estado</label><select name="status" id="status" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3"><option value="">Todos</option>@foreach($statuses as $status)<option value="{{ $status->id }}" @selected(request('status') == $status->id)>{{ $status->name }}</option>@endforeach</select></div>
        <div><label for="mechanic" class="block text-sm font-semibold">Mecánico</label><select name="mechanic" id="mechanic" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3"><option value="">Todos</option>@foreach($mechanics as $mechanic)<option value="{{ $mechanic->id }}" @selected(request('mechanic') == $mechanic->id)>{{ $mechanic->name }}</option>@endforeach</select></div>
        @foreach(['from'=>'Desde','to'=>'Hasta'] as $field=>$label)<div><label for="{{ $field }}" class="block text-sm font-semibold">{{ $label }}</label><input type="date" name="{{ $field }}" id="{{ $field }}" value="{{ request($field) }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3"></div>@endforeach
        <button class="min-h-12 self-end rounded-xl bg-slate-800 px-5 py-3 font-semibold text-white">Buscar</button><a href="{{ route('orders.index') }}" class="inline-flex min-h-12 items-center justify-center self-end rounded-xl border border-slate-300 px-5 py-3">Limpiar</a>
        @if($errors->any())<p class="text-sm text-red-700 sm:col-span-2">{{ $errors->first() }}</p>@endif
    </form>
    <p class="mb-4 text-sm text-slate-500">{{ $orders->total() }} órdenes encontradas.</p>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($orders as $order)
            <article class="flex min-w-0 flex-col rounded-2xl border border-slate-200 p-5 shadow-sm"><div class="flex flex-wrap items-center justify-between gap-2"><span class="font-mono text-sm font-bold text-slate-600">{{ $order->number }}</span><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $order->status->name }}</span></div><h2 class="mt-4 break-words text-lg font-bold text-slate-900">{{ $order->customer->name }}</h2><p class="mt-1 font-semibold text-amber-800">{{ $order->vehicle->license_plate }} · {{ $order->vehicle->brand->name }} {{ $order->vehicle->model }}</p><p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $order->description }}</p><dl class="my-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-sm"><div><dt class="text-slate-500">Mecánico</dt><dd class="mt-1 break-words font-semibold">{{ $order->mechanic?->name ?? 'Sin asignar' }}</dd></div><div><dt class="text-slate-500">Recepción</dt><dd class="mt-1 font-semibold">{{ $order->received_at->format('d/m/Y H:i') }}</dd></div></dl><p class="mb-4 text-sm font-semibold text-slate-700">Subtotal actual: ₡{{ number_format((float)$order->labor_cost + (float)$order->items_sum_line_total, 2, ',', '.') }}</p><a href="{{ route('orders.show',$order) }}" class="mt-auto inline-flex min-h-12 items-center justify-center rounded-xl bg-slate-800 px-4 py-3 font-semibold text-white">Ver orden →</a></article>
        @empty
            <p class="rounded-xl bg-slate-50 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">No hay órdenes que coincidan con la búsqueda.</p>
        @endforelse
    </div><div class="mt-6">{{ $orders->links() }}</div>
</div>
@endsection
