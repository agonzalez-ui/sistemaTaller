@extends('layouts.app')
@section('title', 'Marcas de repuestos')
@section('app-contents')
@if(session('success'))<x-alert :message="session('success')" />@endif
<div class="mt-6"><div class="mb-5 flex flex-wrap justify-between gap-3"><a href="{{ route('spareparts.index') }}" class="inline-flex min-h-12 items-center text-slate-700">← Volver a repuestos</a><a href="{{ route('spare-part-brands.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-400 px-5 py-3 font-semibold">+ Nueva marca</a></div>
<form method="GET" action="{{ route('spare-part-brands.index') }}" class="mb-6 grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-4"><div><label for="search" class="block text-sm font-semibold">Buscar marca</label><input type="search" name="search" id="search" maxlength="80" value="{{ request('search') }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4 py-3">@error('search')<p class="text-sm text-red-700">{{ $message }}</p>@enderror</div><div><label for="status" class="block text-sm font-semibold">Estado</label><select id="status" name="status" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"><option value="">Todos</option><option value="active" @selected(request('status') === 'active')>Activas</option><option value="inactive" @selected(request('status') === 'inactive')>Inactivas</option></select></div><button type="submit" class="min-h-12 self-end rounded-xl bg-slate-800 px-5 py-3 font-semibold text-white">Buscar</button><a href="{{ route('spare-part-brands.index') }}" class="inline-flex min-h-12 items-center justify-center self-end rounded-xl border border-slate-300 px-5 py-3">Limpiar</a></form>
<p class="mb-4 text-sm text-slate-500">{{ $brands->total() }} marcas encontradas.</p><div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
@forelse($brands as $brand)
<article class="min-w-0 rounded-2xl border border-slate-200 p-5"><div class="flex flex-wrap items-center justify-between gap-2"><h2 class="break-words text-lg font-bold text-slate-900">{{ $brand->name }}</h2><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $brand->active ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $brand->active ? 'Activa' : 'Inactiva' }}</span></div><p class="mt-3 text-sm text-slate-500">{{ $brand->spare_parts_count }} repuestos registrados</p><div class="mt-4 flex flex-wrap gap-2"><a href="{{ route('spare-part-brands.edit',$brand) }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-50 px-4 py-3 font-semibold text-amber-800">{{ $brand->active ? 'Editar' : 'Editar / Reactivar' }}</a>
@if($brand->active)
<form action="{{ route('spare-part-brands.destroy',$brand) }}" method="POST" onsubmit="return confirm('¿Desea desactivar esta marca? Las repuestos existentes conservarán sus datos.')">@csrf @method('DELETE')<button type="submit" class="min-h-12 rounded-xl px-4 py-3 font-medium text-red-700 hover:bg-red-50">Desactivar</button></form>
@endif
</div></article>
@empty
<p class="rounded-xl bg-slate-50 p-6 text-slate-500 sm:col-span-2 xl:col-span-3">No hay marcas que coincidan con la búsqueda.</p>
@endforelse
</div><div class="mt-6">{{ $brands->links() }}</div></div>
@endsection
