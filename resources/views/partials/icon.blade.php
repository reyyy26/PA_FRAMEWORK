@php
    $classes = ($classes ?? 'w-5 h-5 text-gray-400');
@endphp

@switch($name)
    @case('home')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9.75 12 3l9 6.75V20a1 1 0 0 1-1 1h-5.25V14h-5.5v7H4a1 1 0 0 1-1-1z" />
        </svg>
        @break
    @case('storefront')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9h18l-1.5 11.25a1 1 0 0 1-1 .75H5.5a1 1 0 0 1-1-.75L3 9Zm2.25-6h13.5L21 9H3l2.25-6Zm4.5 9v7.5m6-7.5v7.5" />
        </svg>
        @break
    @case('clock')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l3.5 2m5.5-2A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z" />
        </svg>
        @break
    @case('shield')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21c-5.333-2-8-5.667-8-11V6.5L12 3l8 3.5V10c0 5.333-2.667 9-8 11Zm-3-9 2 2 4-4" />
        </svg>
        @break
    @case('chip')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4V2m8 2V2m-4 6v4m-6 2h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2Zm12-6h2m-2 8h2M4 10H2m2 8H2m4 4v-2m8 2v-2" />
        </svg>
        @break
    @case('clipboard')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 4h6l1 2h3a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h3l1-2Zm0 7h6M9 15h6" />
        </svg>
        @break
    @case('globe')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9 9 0 1 1 0-18 9 9 0 0 1 0 18Zm0 0c2.5-2.5 4-5.667 4-9s-1.5-6.5-4-9m0 18c-2.5-2.5-4-5.667-4-9s1.5-6.5 4-9m9 9H3" />
        </svg>
        @break
    @case('pos')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Zm3-4h10a1 1 0 0 1 1 1v3H6V4a1 1 0 0 1 1-1Zm3 12h4" />
        </svg>
        @break
    @case('layers')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 9 9-6 9 6-9 6-9-6Zm0 6 9 6 9-6" />
        </svg>
        @break
    @case('chart')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 19h16M8 17V9m4 8V5m4 12v-6" />
        </svg>
        @break
    @case('menu')
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        @break
    @default
        <span class="{{ $classes }}">•</span>
@endswitch
