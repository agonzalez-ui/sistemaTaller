@props(['name'])
<svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-6 w-6']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('security')
            <path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6z" /><path d="m8 12 3 3 5-6" />
            @break
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
            @break
        @case('customers')
            <circle cx="9" cy="7" r="4" /><path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2M16 3.2a4 4 0 0 1 0 7.6M22 21v-2a4 4 0 0 0-3-3.9" />
            @break
        @case('vehicles')
            <circle cx="5" cy="16" r="3" /><circle cx="19" cy="16" r="3" /><path d="M7.5 14h5l4-4H6l1.5 4 4-4M13 6h2l4 10" />
            @break
        @case('parts')
            <path d="M14.7 6.3a5 5 0 0 0-6.5 6.5L3 18a2.1 2.1 0 0 0 3 3l5.2-5.2a5 5 0 0 0 6.5-6.5l-3 3-3-3z" />
            @break
        @case('orders')
            <rect x="8" y="2" width="8" height="4" rx="1" /><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2m-8 7 1 1 2-2m3 1h2m-8 6 1 1 2-2m3 1h2" />
            @break
        @case('invoices')
            <path d="M5 3h14v18l-2-1.5-2 1.5-3-1.5L9 21l-2-1.5L5 21V3zM9 7h6M9 11h6M9 15h2m3 0h1" />
            @break
    @endswitch
</svg>