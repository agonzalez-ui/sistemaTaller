@if ($errors->any())
    <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <p class="font-semibold">Revise los siguientes datos:</p>
        <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid gap-5 sm:grid-cols-2">
    @php
        $fields = [
            ['name', 'Nombre completo', 'text', 150, true],
            ['email', 'Correo electrónico', 'email', 150, true],
            ['identification_number', 'Cédula o DIMEX (opcional)', 'text', 12, false],
            ['username', 'Nombre de usuario (opcional)', 'text', 60, false],
        ];
    @endphp
    @foreach ($fields as [$field, $label, $type, $length, $required])
        <div>
            <label for="{{ $field }}" class="block text-sm font-semibold text-slate-700">{{ $label }} @if ($required)<span class="text-red-600">*</span>@endif</label>
            <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" maxlength="{{ $length }}"
                   value="{{ old($field, isset($user) ? $user->$field : '') }}" @required($required)
                   @if ($field === 'identification_number') inputmode="numeric" pattern="([1-9][0-9]{8}|[1-9][0-9]{10,11})" @endif
                   class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3 py-3 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endforeach

    @php($isSelf = isset($user) && auth()->id() === $user->id)
    <div>
        <label for="role_id" class="block text-sm font-semibold text-slate-700">Rol <span class="text-red-600">*</span></label>
        @if ($isSelf)<input type="hidden" name="role_id" value="{{ $user->role_id }}">@endif
        <select id="role_id" name="role_id" required @disabled($isSelf)
                class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3 py-3 disabled:bg-slate-100 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            <option value="">Seleccione un rol</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((string) old('role_id', $user->role_id ?? '') === (string) $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
        @if ($isSelf)<p class="mt-1 text-xs text-slate-500">Su propio rol está protegido para conservar el acceso.</p>@endif
        @error('role_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="joined_at" class="block text-sm font-semibold text-slate-700">Fecha de ingreso (opcional)</label>
        <input id="joined_at" name="joined_at" type="date" value="{{ old('joined_at', isset($user) ? $user->joined_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
               class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3 py-3 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
        @error('joined_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    @foreach (['password' => 'Contraseña', 'password_confirmation' => 'Confirmar contraseña'] as $field => $label)
        <div>
            <label for="{{ $field }}" class="block text-sm font-semibold text-slate-700">{{ $label }}</label>
            <input id="{{ $field }}" name="{{ $field }}" type="password" autocomplete="new-password" minlength="8" maxlength="255" @required(!isset($user))
                   class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3 py-3 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            @if ($field === 'password')<p class="mt-1 text-xs leading-5 text-slate-500">Mínimo 8 caracteres, con letras y números. @if(isset($user))Déjela vacía para conservar la contraseña actual.@endif</p>@endif
            @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endforeach
</div>

<div class="grid gap-3 sm:grid-cols-2">
    <div class="rounded-xl border border-slate-200 p-4">
        <input type="hidden" name="active" value="{{ $isSelf ? 1 : 0 }}">
        <label class="flex min-h-12 cursor-pointer items-center gap-3">
            <input type="checkbox" name="active" value="1" @checked($isSelf || old('active', $user->active ?? true)) @disabled($isSelf) class="h-5 w-5 accent-amber-500">
            <span class="text-sm font-semibold text-slate-700">Usuario activo</span>
        </label>
        <p class="text-xs leading-5 text-slate-500">Una cuenta deshabilitada no puede acceder al sistema.</p>
    </div>
    <div class="rounded-xl border border-slate-200 p-4">
        <input type="hidden" name="verified" value="0">
        <label class="flex min-h-12 cursor-pointer items-center gap-3">
            <input type="checkbox" name="verified" value="1" @checked(old('verified', isset($user) && $user->email_verified_at !== null)) class="h-5 w-5 accent-amber-500">
            <span class="text-sm font-semibold text-slate-700">Correo verificado por el administrador</span>
        </label>
        <p class="text-xs leading-5 text-slate-500">Marque esta opción solo si confirmó el correo. De lo contrario, el usuario debe verificarlo al ingresar.</p>
    </div>
</div>
<p class="text-xs leading-5 text-slate-500">El inicio de sesión utiliza el correo electrónico y la contraseña. El nombre de usuario es un identificador interno opcional.</p>