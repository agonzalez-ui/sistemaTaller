@extends('layouts.app')
@section('title', 'Editar marca de repuesto')
@section('content-width', 'max-w-3xl')
@section('app-contents')
<form class="mt-6 space-y-5" action="{{ route('spare-part-brands.update', $brand) }}" method="POST">
@csrf
@method('PUT')
@include('spare-part-brands._form')
<div class="flex flex-wrap gap-3"><button type="submit" class="min-h-12 rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950">Guardar marca</button><a href="{{ route('spare-part-brands.index') }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-5 py-3">Cancelar</a></div></form>
@endsection
