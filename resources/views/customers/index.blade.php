@extends('layouts.app')

@section('title')
    Clientes
@endsection

@section('app-contents')
    @if (session('success'))
        <x-alert :message="session('success')" />
    @endif

    <div class="mt-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">
                Lista de clientes
            </h2>

            @can('module-access', ['customers', 'create'])
            <a href="{{ route('customers.create') }}"
                class="rounded-lg bg-amber-500 px-4 py-2 font-semibold
                      text-slate-950 transition hover:bg-amber-400">
                + Nuevo cliente
            </a>
            @endcan
        </div>

        <div class="rounded-xl border border-slate-200">
            <table class="block w-full border-collapse text-left text-sm md:table md:table-fixed">
                <thead class="hidden bg-slate-50 text-slate-600 md:table-header-group">
                    <tr class="border-b border-slate-200">
                        <th scope="col" class="w-[35%] px-4 py-3">
                            Nombre completo
                        </th>
                        <th scope="col" class="w-[18%] px-4 py-3">
                            Cédula / DIMEX
                        </th>
                        <th scope="col" class="w-[30%] px-4 py-3">
                            Contacto
                        </th>
                        <th scope="col" class="w-[17%] px-4 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="block md:table-row-group">
                    @forelse ($customers as $customer)
                        <tr
                            class="block border-b border-slate-100 md:table-row
                                   transition last:border-b-0 hover:bg-amber-50">

                            <td class="block px-4 py-4 align-top md:table-cell">
                                <span class="mb-1 block text-xs text-slate-500 md:hidden">Nombre completo</span>
                                <span
                                    class="block [overflow-wrap:anywhere] text-base
                                             font-semibold leading-6 text-slate-900">
                                    {{ $customer->name }}
                                </span>
                            </td>

                            <td
                                class="block break-all px-4 py-3 md:table-cell md:py-4
                                       align-top text-slate-600">
                                <span class="mb-1 block text-xs text-slate-500 md:hidden">Cédula / DIMEX</span>
                                {{ $customer->identification_number }}
                            </td>

                            <td class="block px-4 py-3 align-top md:table-cell md:py-4">
                                <span class="mb-1 block text-xs text-slate-500 md:hidden">Contacto</span>
                                <div class="space-y-1">
                                    <p class="break-words text-slate-700">
                                        {{ $customer->phones->pluck('phone')->implode(', ') ?: 'Sin teléfono' }}
                                    </p>

                                    <p class="break-all text-xs text-slate-500">
                                        {{ $customer->email ?: 'Sin correo' }}
                                    </p>
                                </div>
                            </td>

                            <td class="block px-4 py-4 align-top md:table-cell">
                                <div class="flex flex-wrap items-center gap-3">
                                    @can('module-access', ['customers', 'edit'])
                                    <a href="{{ route('customers.edit', $customer) }}"
                                        class="font-medium text-amber-700 hover:underline">
                                        Editar
                                    </a>
                                    @endcan

                                    @can('module-access', ['customers', 'delete'])
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                        onsubmit="return confirm('¿Seguro que desea eliminar este cliente?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="font-medium text-red-600 hover:underline">
                                            Eliminar
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="block md:table-row">
                            <td colspan="4" class="block px-4 py-8 text-center text-slate-500 md:table-cell">
                                Todavía no hay clientes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $customers->links() }}
        </div>
    </div>
@endsection
