@props(['navigation', 'desktop' => false])
@foreach ($navigation as [$label, $route, $pattern, $icon])
    @php($active = request()->routeIs($pattern))
    <li class="min-w-0">
        <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
            @class([
                'flex min-h-12 items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2',
                'justify-center px-2' => $desktop,
                'bg-amber-400 text-slate-950 shadow-sm' => $active,
                'text-slate-600 hover:bg-slate-100 active:bg-slate-200' => !$active,
            ])>
            <x-module-icon :name="$icon" class="h-5 w-5 shrink-0" />
            {{ $label }}
        </a>
    </li>
@endforeach