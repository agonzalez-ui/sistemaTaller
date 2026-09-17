@extends('layouts.app')

@section('content-width', 'max-w-3xl')

@section('title')
    Editar cliente
@endsection

@section('app-contents')
    <div class="mt-6">
        <p class="mb-6 text-sm text-slate-500">
            Actualice la información de {{ $customer->name }}.
        </p>

        <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('customers._form')

            <div class="flex flex-wrap items-center gap-3
                        border-t border-slate-200 pt-5">
                <button type="submit"
                    class="rounded-lg bg-amber-500 px-5 py-2.5
                               font-semibold text-slate-950 transition
                               hover:bg-amber-400
                               focus-visible:outline-none focus-visible:ring-2
                               focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                    Actualizar cliente
                </button>

                <a href="{{ route('customers.index') }}"
                    class="rounded-lg border border-slate-300 px-5 py-2.5
                          font-medium text-slate-600 transition
                          hover:bg-slate-50
                          focus-visible:outline-none focus-visible:ring-2
                          focus-visible:ring-slate-400 focus-visible:ring-offset-2">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
