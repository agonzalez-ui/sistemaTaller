@extends('layouts.auth')

@section('title', 'Nueva contraseña')

@section('auth-contents')
    <p class="mt-4 text-sm leading-6 text-slate-600">Cree una contraseña de al menos 8 caracteres que incluya letras y números.</p>

    <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div><label for="email" class="block text-lg font-bold">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" maxlength="150" class="mt-2 min-h-12 w-full rounded-lg border border-slate-300 px-4"><x-input-error field="email" /></div>
        <div><label for="password" class="block text-lg font-bold">Contraseña nueva</label><input id="password" name="password" type="password" required autocomplete="new-password" class="mt-2 min-h-12 w-full rounded-lg border border-slate-300 px-4"><x-input-error field="password" /></div>
        <div><label for="password_confirmation" class="block text-lg font-bold">Confirmar contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-2 min-h-12 w-full rounded-lg border border-slate-300 px-4"></div>
        <button class="min-h-12 w-full rounded-lg bg-amber-600 px-5 py-3 font-bold text-white hover:bg-amber-700">Actualizar contraseña</button>
    </form>
@endsection
