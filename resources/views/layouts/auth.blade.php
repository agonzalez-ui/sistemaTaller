@extends('layouts.base')

@section('contents')
    <div
        class="min-h-[calc(100dvh-178px)] bg-cover bg-center bg-no-repeat px-4 py-10"
        style="background-image: url('{{ asset('img/fondo-auth.png') }}')"
    >
        <main class="max-w-2xl mx-auto bg-white p-10 rounded-lg shadow-lg">
            <h1 class="font-bold text-4xl">@yield('title')</h1>
            @yield('auth-contents')
        </main>
    </div>
@endsection