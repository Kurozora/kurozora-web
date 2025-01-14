@props(['active', 'disabled' => false])

@php
    $classes = ($active ?? false)
                ? 'inline-flex items-center h-full pl-1 pr-1 pt-1 border-b-2 border-tint text-sm font-medium whitespace-nowrap leading-5 text-primary focus:outline-none focus:border-tint transition duration-150 ease-in-out'
                : 'inline-flex items-center h-full pl-1 pr-1 pt-1 border-b-2 border-transparent text-sm font-medium whitespace-nowrap leading-5 text-secondary hover:text-primary hover:border-primary focus:outline-none focus:text-primary focus:border-primary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
