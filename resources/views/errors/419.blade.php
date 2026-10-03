@extends('errors.layout')
@section('code', '419')
@section('title', 'La sesión venció')
@section('message', 'La página permaneció abierta durante mucho tiempo y la sesión de seguridad dejó de ser válida.')
@section('suggestion', 'Vuelva al inicio y repita la acción. Los datos que no se hayan enviado podrían necesitar ingresarse nuevamente.')
@section('icon')
    <svg class="h-9 w-9" style="width:2.25rem;height:2.25rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
@endsection

