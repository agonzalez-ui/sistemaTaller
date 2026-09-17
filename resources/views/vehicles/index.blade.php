@extends('layouts.app')
@section('title', 'Motos')
@section('app-contents')
@if(session('success'))<x-alert :message="session('success')" />@endif
<div class="mt-6">
@can('manage-security')<a href="{{ route('vehicle-brands.index') }}" class="mb-4 inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 hover:bg-slate-50">Administrar marcas de motos →</a>@endcan

<div class="mb-5 flex flex-wrap items-center justify-between gap-3"><div><h2 class="text-lg font-semibold text-slate-900">Motos del taller</h2><p class="mt-1 text-sm text-slate-500">{{ $vehicles->total() }} motos encontradas. Consulte sus datos y propietarios.</p></div>@can('module-access', ['vehicles','create'])<a href="{{ route('vehicles.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950 hover:bg-amber-300">+ Nueva moto</a>@endcan</div>
<form method="GET" action="{{ route('vehicles.index') }}" class="mb-6 grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_180px_auto_auto]">
<div><label for="search" class="mb-1 block text-sm font-medium text-slate-700">Buscar moto o propietario</label><input type="search" id="search" name="search" maxlength="100" value="{{ request('search') }}" placeholder="Placa, marca, modelo o cliente" class="min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"></div>
<div><label for="status" class="mb-1 block text-sm font-medium text-slate-700">Estado</label><select name="status" id="status" class="min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3 py-3"><option value="">Todos</option><option value="active" @selected(request('status') === 'active')>Activas</option><option value="inactive" @selected(request('status') === 'inactive')>Inactivas</option></select></div>
<button class="min-h-12 self-end rounded-xl bg-slate-800 px-5 py-3 font-semibold text-white" type="submit">Buscar</button><a href="{{ route('vehicles.index') }}" class="inline-flex min-h-12 items-center justify-center self-end rounded-xl border border-slate-300 px-4 py-3 text-slate-700">Limpiar</a>
@error('search')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
</form>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
@forelse($vehicles as $vehicle)
<article class="flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
<div class="flex flex-wrap items-center justify-between gap-2"><span class="rounded-lg bg-slate-900 px-3 py-2 text-lg font-bold tracking-wide text-white">{{ $vehicle->license_plate }}</span><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $vehicle->active ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $vehicle->active ? 'Activa' : 'Inactiva' }}</span></div>
<h3 class="mt-4 break-words text-lg font-semibold text-slate-900">{{ $vehicle->brand->name }} {{ $vehicle->model }}</h3><p class="mt-1 text-sm text-slate-500">Año {{ $vehicle->year }} · {{ $vehicle->color ?: 'Color sin registrar' }}</p>
<div class="my-4 rounded-xl bg-slate-50 p-3"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Propietario</p><p class="mt-1 break-words font-semibold text-slate-900">{{ $vehicle->customer->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $vehicle->customer->identification_number }}</p></div>
<p class="mb-3 text-xs text-slate-500">{{ $vehicle->orders_count }} órdenes registradas</p>
<div class="mt-auto flex flex-wrap gap-2 border-t border-slate-100 pt-3">@can('module-access', ['vehicles','edit'])<a href="{{ route('vehicles.edit', $vehicle) }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-50 px-4 py-3 font-semibold text-amber-800 hover:bg-amber-100">Editar</a>@endcan
@can('module-access', ['vehicles','delete'])@if($vehicle->active)<form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('¿Desea desactivar esta moto? Se conservará su historial.')">@csrf @method('DELETE')<button type="submit" class="min-h-12 rounded-xl px-4 py-3 font-medium text-red-700 hover:bg-red-50">Desactivar</button></form>@endif
@endcan</div>
</article>
@empty
<div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">No hay motos que coincidan con la búsqueda.@if(!request('search') && !request('status')) Registre la primera moto del taller.@endif</div>
@endforelse
</div><div class="mt-6">
@can('manage-security')<a href="{{ route('vehicle-brands.index') }}" class="mb-4 inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-4 py-3 font-semibold text-slate-700 hover:bg-slate-50">Administrar marcas de motos →</a>@endcan
{{ $vehicles->links() }}</div>
</div>
@endsection
