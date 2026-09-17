@if ($errors->any())
    <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700">Nombre del rol <span class="text-red-600">*</span></label>
        <input id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required maxlength="100" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3 py-3 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
    </div>
    <div>
        <label for="description" class="block text-sm font-semibold text-slate-700">Descripción <span class="text-red-600">*</span></label>
        <textarea id="description" name="description" required maxlength="250" rows="2" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">{{ old('description', $role->description ?? '') }}</textarea>
    </div>
</div>
@php($administrator = isset($role) && $role->is_administrator)
<div>
    <input type="hidden" name="active" value="{{ $administrator ? 1 : 0 }}">
    <label class="flex min-h-12 cursor-pointer items-center gap-3 text-sm font-semibold text-slate-700">
        <input type="checkbox" name="active" value="1" @checked($administrator || old('active', $role->active ?? true)) @disabled($administrator) class="h-5 w-5 accent-amber-500"> Rol activo
    </label>
    <p class="text-xs text-slate-500">Deshabilitar el rol impide el acceso a todos sus usuarios.</p>
</div>
<div>
    <h2 class="text-lg font-bold text-slate-900">Permisos por módulo</h2>
    <p class="mt-2 text-sm leading-6 text-slate-500">Marque “Ver” para habilitar el módulo y seleccione las acciones permitidas. Usuarios y roles están reservados al administrador.</p>
    @if ($administrator)
        <p class="mt-3 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">El administrador conserva acceso completo. Este rol está protegido.</p>
    @endif
    <div class="mt-4 space-y-3">
        @foreach ($modules as $module)
            @php($permission = isset($role) ? $role->modules->firstWhere('id', $module->id)?->pivot : null)
            <fieldset class="rounded-xl border border-slate-200 px-4 pb-3">
                <legend class="px-1 pt-3 text-sm font-semibold text-slate-800">{{ $module->name }}</legend>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (['can_view' => 'Ver', 'can_create' => 'Crear', 'can_edit' => 'Modificar', 'can_delete' => 'Eliminar / deshabilitar'] as $action => $label)
                        <input type="hidden" name="permissions[{{ $module->id }}][{{ $action }}]" value="0">
                        <label class="flex min-h-12 cursor-pointer items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="permissions[{{ $module->id }}][{{ $action }}]" value="1" @checked($administrator || old("permissions.{$module->id}.{$action}", $permission?->$action ?? false)) @disabled($administrator) class="h-5 w-5 shrink-0 accent-amber-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </div>
</div>