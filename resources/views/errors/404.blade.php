@extends('errors.layout')
@section('code', '404')
@section('title', 'No encontramos esa página')
@section('message', 'La dirección puede estar incompleta, haber cambiado o ya no estar disponible.')
@section('suggestion', 'Compruebe la dirección o vuelva al inicio para continuar trabajando en SIMRH.')
@section('icon')
    <svg class="h-9 w-9" style="width:2.25rem;height:2.25rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4M11 8v3l2 2"/></svg>
@endsection

