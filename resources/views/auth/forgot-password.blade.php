@extends('layouts.auth')

@section('title', 'Recuperar contraseña')

@section('auth-contents')
    <p class="mt-4 text-sm leading-6 text-slate-600">Ingrese el correo asociado a su cuenta. Si está activa, enviaremos un enlace temporal para crear una contraseña nueva.</p>

    @if (session('success'))
        <div class="mt-5"><x-alert :message="session('success')" /></div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-lg font-bold">Correo electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" maxlength="150" class="mt-2 min-h-12 w-full rounded-lg border border-slate-300 px-4" placeholder="nombre@correo.com">
            <x-input-error field="email" />
        </div>
        <button class="min-h-12 w-full rounded-lg bg-amber-600 px-5 py-3 font-bold text-white hover:bg-amber-700">Enviar enlace de recuperación</button>
    </form>

    <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-12 items-center font-semibold text-slate-600 hover:text-amber-700">← Volver al inicio de sesión</a>
@endsection
