@extends('errors.layout')
@section('code', '500')
@section('title', 'No pudimos completar la operación')
@section('message', 'El sistema encontró un inconveniente inesperado mientras procesaba la solicitud.')
@section('suggestion', 'Intente nuevamente. Si el problema continúa, informe al responsable técnico indicando qué acción estaba realizando.')
@section('icon-colors', 'bg-red-100 text-red-700')
@section('icon')
    <svg class="h-9 w-9" style="width:2.25rem;height:2.25rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5 5L3 18v3h3l6.7-6.7a4 4 0 0 0 5-5l-2.4 2.4-3-3 2.4-2.4Z"/></svg>
@endsection
@section('secondary-action')
    <button type="button" onclick="window.location.reload()" class="min-h-12 rounded-xl border border-slate-300 bg-white px-6 py-3 font-bold text-slate-700 hover:bg-slate-50">Intentar de nuevo</button>
@endsection

