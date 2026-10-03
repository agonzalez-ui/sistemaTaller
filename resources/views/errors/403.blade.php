@extends('errors.layout')
@section('code', '403')
@section('title', 'Acceso restringido')
@section('message', 'Su cuenta no tiene permiso para consultar esta sección o realizar esta acción.')
@section('suggestion', 'Regrese al panel y utilice uno de los módulos habilitados para su rol. Si necesita acceso, comuníquese con el administrador del taller.')
@section('icon-colors', 'bg-red-100 text-red-700')
@section('icon')
    <svg class="h-9 w-9" style="width:2.25rem;height:2.25rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
@endsection

