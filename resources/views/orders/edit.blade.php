@extends('layouts.app')
@section('title', 'Editar orden '.$order->number)
@section('content-width', 'max-w-5xl')
@section('app-contents')
<form method="POST" action="{{ route('orders.update',$order) }}" class="mt-6 space-y-6">@csrf @method('PUT') @include('orders._form')
<div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5"><button class="min-h-12 rounded-xl bg-amber-400 px-5 py-3 font-semibold">Actualizar orden</button><a href="{{ route('orders.show',$order) }}" class="inline-flex min-h-12 items-center rounded-xl border border-slate-300 px-5 py-3">Cancelar</a></div></form>
@endsection
