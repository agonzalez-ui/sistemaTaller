@extends('layouts.app')

@section('title', 'Nuevo rol')
@section('content-width', 'max-w-4xl')

@section('app-contents')
    <div class="mt-6">
        <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('roles._form')
            <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-amber-400 px-5 py-3 font-semibold text-slate-950 hover:bg-amber-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Guardar rol</button>
                <a href="{{ route('roles.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-600 hover:bg-slate-50">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
