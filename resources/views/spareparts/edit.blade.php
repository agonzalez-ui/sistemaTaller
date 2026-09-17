@extends('layouts.app')
@section('title', 'Editar repuesto')
@section('content-width', 'max-w-4xl')
@section('app-contents')
<form class="mt-6 space-y-6" action="{{ route('spareparts.update', $part) }}" method="POST">
@csrf
@method('PUT')
@include('spareparts._form')
<div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5"><button type="submit" class="min-h-12 rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950">Guardar repuesto</button><a href="{{ route('spareparts.index') }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-5 py-3">Cancelar</a></div></form>
@endsection
