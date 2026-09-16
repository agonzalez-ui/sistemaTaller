@extends('layouts.auth')

@section('title')
    Crear Cuenta
@endsection

@section('auth-contents')
    <form method="POST" class="mt-7 space-y-3" novalidate action="{{ route('register.store') }}">
        <div class="space-y-2">
            <label class="font-bold text-2xl block" for="name">Nombre</label>

            <input id="name" type="text" placeholder="Tu Nombre" class="w-full border border-gray-300 p-3 rounded-lg"
                name="name" value="{{ old('name') }}" />
        </div>

        <x-input-error field="name" />

        <div class="space-y-2">
            <label class="font-bold text-2xl block" for="email">Email</label>

            <input id="email" type="email" placeholder="Email de Registro"
                class="w-full border border-gray-300 p-3 rounded-lg" name="email" value="{{ old('email') }}" />
        </div>

        <x-input-error field="email" />

        <div class="space-y-2">
            <label class="font-bold text-2xl block">Contraseña</label>

            <input type="password" placeholder="Contraseña de Registro" class="w-full border border-gray-300 p-3 rounded-lg"
                name="password" />
        </div>

        <x-input-error field="password" />

        <div class="space-y-2">
            <label class="font-bold text-2xl block" for="password_confirmation">Repetir Contraseña</label>

            <input type="password" placeholder="Contraseña de Registro" class="w-full border border-gray-300 p-3 rounded-lg"
                name="password_confirmation" />
        </div>

        <input type="submit" value='Registrarme'
            class="bg-[#D97706] hover:bg-[#B85F05] w-full p-3 rounded-lg text-white font-bold  text-xl cursor-pointer" />
    </form>
@endsection
