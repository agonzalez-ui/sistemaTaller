@props(['navigation', 'desktop' => false, 'dark' => false])
@foreach ($navigation as [$label, $route, $pattern, $icon])
    @php($active = request()->routeIs($pattern))
    <li class="min-w-0">
        <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
            @class([
                'flex min-h-11 items-center gap-3 rounded-xl px-4 py-2 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2',
                'justify-center px-2' => $desktop,
                'bg-amber-400 text-slate-950 shadow-sm' => $active,
                'text-slate-200 hover:bg-white/10 hover:text-white active:bg-white/15' => !$active && $dark,
                'text-slate-600 hover:bg-slate-100 active:bg-slate-200' => !$active && !$dark,
            ])>
            <x-module-icon :name="$icon" class="h-5 w-5 shrink-0" />
            {{ $label }}
        </a>
    </li>
@endforeach
