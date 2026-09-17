@extends('layouts.app')
@section('title', 'Usuarios')

@section('app-contents')
    @if (session('success'))<div class="mt-5"><x-alert :message="session('success')" /></div>@endif
    @if ($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Gestione el equipo, sus roles y el acceso al taller.</p>
        <a href="{{ route('users.create') }}" class="inline-flex min-h-12 items-center rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950 hover:bg-amber-300">+ Nuevo usuario</a>
    </div>
    <form action="{{ route('users.index') }}" method="GET" class="mt-6 grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2"><label for="search" class="mb-1 block text-xs font-semibold text-slate-500">Buscar usuario</label><input id="search" name="search" value="{{ request('search') }}" maxlength="150" placeholder="Nombre, cédula, usuario o correo" class="min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm"></div>
        <div><label for="role" class="mb-1 block text-xs font-semibold text-slate-500">Rol</label><select id="role" name="role" class="min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm"><option value="">Todos los roles</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((string) request('role') === (string) $role->id)>{{ $role->name }}</option>@endforeach</select></div>
        <div><label for="status" class="mb-1 block text-xs font-semibold text-slate-500">Estado</label><select id="status" name="status" class="min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm"><option value="">Todos</option><option value="active" @selected(request('status') === 'active')>Activos</option><option value="inactive" @selected(request('status') === 'inactive')>Deshabilitados</option></select></div>
        <div class="flex items-end gap-3"><button class="min-h-12 rounded-lg bg-[#1E3A5F] px-4 py-3 text-sm font-semibold text-white" type="submit">Buscar</button><a href="{{ route('users.index') }}" class="inline-flex min-h-12 items-center text-sm text-slate-600 hover:underline">Limpiar</a></div>
    </form>
    <p class="mt-5 text-xs font-semibold text-slate-500">{{ $users->total() }} resultados</p>
    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($users as $user)
            <article class="flex min-w-0 flex-col rounded-2xl border border-slate-200 p-5">
                <div class="flex items-start gap-3">
                    <span aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-lg font-bold text-amber-800">{{ mb_substr($user->name, 0, 1) }}</span>
                    <div class="min-w-0"><h2 class="break-words text-lg font-bold text-slate-900">{{ $user->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $user->role?->name ?? 'Sin rol asignado' }}</p></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span @class(['rounded-full px-3 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $user->active, 'bg-red-50 text-red-700' => !$user->active])>{{ $user->active ? 'Activo' : 'Deshabilitado' }}</span>
                    @if($user->role && !$user->role->active)<span class="rounded-full bg-red-50 px-3 py-1 text-xs text-red-700">Rol deshabilitado</span>@endif
                    @if(!$user->email_verified_at)<span class="rounded-full bg-amber-50 px-3 py-1 text-xs text-amber-800">Correo pendiente</span>@endif
                    @if(auth()->id() === $user->id)<span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">Su cuenta</span>@endif
                </div>
                <dl class="mt-4 space-y-2 text-sm"><div><dt class="text-xs text-slate-400">Correo</dt><dd class="break-all text-slate-700">{{ $user->email }}</dd></div><div><dt class="text-xs text-slate-400">Cédula / DIMEX</dt><dd class="text-slate-700">{{ $user->identification_number ?: 'Sin registrar' }}</dd></div></dl>
                <div class="mt-auto flex flex-wrap gap-3 pt-5">
                    <a href="{{ route('users.edit', $user) }}" class="inline-flex min-h-12 items-center rounded-lg bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">{{ $user->active ? 'Modificar' : 'Modificar / activar' }}</a>
                    @if ($user->active && auth()->id() !== $user->id)
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Deshabilitar este usuario? Perderá acceso al sistema. Su historial se conservará.')">@csrf @method('DELETE')<button type="submit" class="min-h-12 rounded-lg px-3 py-3 text-sm font-semibold text-red-600 hover:bg-red-50">Deshabilitar</button></form>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-xl bg-slate-50 p-6 text-sm text-slate-500 sm:col-span-2 lg:col-span-3">No se encontraron usuarios con esos filtros.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
@endsection