@extends('layouts.app')
@section('title', 'Roles y permisos')

@section('app-contents')
    @if (session('success'))<div class="mt-5"><x-alert :message="session('success')" /></div>@endif
    @if ($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <div class="mt-5 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500">Defina qué puede consultar y modificar cada integrante del taller.</p><a href="{{ route('roles.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950 hover:bg-amber-300">+ Nuevo rol</a></div>
    <form action="{{ route('roles.index') }}" method="GET" class="mt-6 grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-3">
        <div><label for="search" class="mb-1 block text-xs font-semibold text-slate-500">Buscar rol</label><input id="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Nombre o descripción" class="min-h-12 w-full rounded-lg border border-slate-300 px-3 py-3 text-sm"></div>
        <div><label for="status" class="mb-1 block text-xs font-semibold text-slate-500">Estado</label><select id="status" name="status" class="min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm"><option value="">Todos</option><option value="active" @selected(request('status') === 'active')>Activos</option><option value="inactive" @selected(request('status') === 'inactive')>Deshabilitados</option></select></div>
        <div class="flex items-end gap-3"><button class="min-h-12 rounded-lg bg-[#1E3A5F] px-4 py-3 text-sm font-semibold text-white" type="submit">Buscar</button><a href="{{ route('roles.index') }}" class="inline-flex min-h-12 items-center text-sm text-slate-600 hover:underline">Limpiar</a></div>
    </form>
    <p class="mt-5 text-xs font-semibold text-slate-500">{{ $roles->total() }} resultados</p>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($roles as $role)
            <article class="flex min-w-0 flex-col rounded-2xl border border-slate-200 p-5">
                <div class="flex items-start gap-3"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800"><x-module-icon name="security" /></span><div><h2 class="break-words text-lg font-bold text-slate-900">{{ $role->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $role->users_count }} usuarios asignados</p></div></div>
                <p class="mt-4 text-sm leading-6 text-slate-500">{{ $role->description }}</p>
                <div class="mt-4 flex flex-wrap gap-2"><span @class(['rounded-full px-3 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $role->active, 'bg-red-50 text-red-700' => !$role->active])>{{ $role->active ? 'Activo' : 'Deshabilitado' }}</span>@if($role->is_administrator)<span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Protegido</span>@endif</div>
                <div class="mt-auto flex flex-wrap gap-3 pt-5"><a href="{{ route('roles.edit', $role) }}" class="inline-flex min-h-12 items-center rounded-lg bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">Modificar / permisos</a>
                    @if ($role->active && !$role->is_administrator)<form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('¿Deshabilitar este rol? Todos sus usuarios perderán acceso al sistema.')">@csrf @method('DELETE')<button class="min-h-12 rounded-lg px-3 py-3 text-sm font-semibold text-red-600 hover:bg-red-50" type="submit">Deshabilitar</button></form>@endif
                </div>
            </article>
        @empty
            <p class="rounded-xl bg-slate-50 p-6 text-sm text-slate-500 sm:col-span-2 lg:col-span-3">No se encontraron roles con esos filtros.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $roles->links() }}</div>
@endsection