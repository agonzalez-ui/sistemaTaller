@extends('layouts.app')
@section('title', 'Editar moto')
@section('content-width', 'max-w-4xl')
@section('app-contents')
<div class="mt-6"><form action="{{ route('vehicles.update', $vehicle) }}" method="POST" class="space-y-6">
@csrf
@method('PUT')
@include('vehicles._form')
<div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5"><button class="min-h-12 rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950 hover:bg-amber-300" type="submit">Actualizar moto</button><a href="{{ route('vehicles.index') }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-5 py-3 text-slate-700">Cancelar</a></div></form></div>
@endsection

