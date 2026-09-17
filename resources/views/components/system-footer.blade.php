<footer class="border-t border-slate-200 bg-white text-slate-600">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-5 text-center sm:flex-row sm:px-6 sm:text-left lg:px-8">
        <div>
            <p class="text-sm font-semibold text-slate-800">{{ config('simrh.acronym') }} <span class="font-normal text-slate-500">· Gestión del taller</span></p>
            <p class="mt-1 text-xs text-slate-500">© {{ now()->year }} {{ config('simrh.owner') }} · Versión {{ config('simrh.version') }} · {{ config('simrh.status') }}</p>
        </div>
        <nav aria-label="Información del sistema" class="flex flex-wrap items-center justify-center gap-2">
            @can('module-access', ['about', 'view'])
                <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif class="inline-flex min-h-12 items-center rounded-xl px-4 py-3 text-sm font-semibold hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">Acerca de</a>
            @endcan
            @can('module-access', ['help', 'view'])
                <a href="{{ route('help') }}" @if(request()->routeIs('help')) aria-current="page" @endif class="inline-flex min-h-12 items-center rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">Ayuda</a>
            @endcan
        </nav>
    </div>
</footer>