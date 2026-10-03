@extends('errors.layout')
@section('code', '429')
@section('title', 'Demasiados intentos')
@section('message', 'Se realizaron varias solicitudes en poco tiempo y el sistema activó una protección temporal.')
@section('suggestion', 'Espere un minuto antes de intentarlo nuevamente. Evite presionar varias veces el mismo botón.')
@section('icon-colors', 'bg-orange-100 text-orange-700')
@section('icon')
    <svg class="h-9 w-9" style="width:2.25rem;height:2.25rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v5M12 18h.01"/></svg>
@endsection

