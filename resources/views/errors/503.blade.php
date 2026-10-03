@extends('errors.layout')
@section('code', '503')
@section('title', 'Sistema en mantenimiento')
@section('message', 'SIMRH no está disponible temporalmente mientras realizamos tareas de mantenimiento.')
@section('suggestion', 'Espere unos minutos y vuelva a intentarlo. La información registrada permanece protegida.')
@section('icon-colors', 'bg-blue-100 text-blue-700')
@section('icon')
    <svg class="h-9 w-9" style="width:2.25rem;height:2.25rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg>
@endsection

