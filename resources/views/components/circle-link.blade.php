@props(['color' => null, 'disabled' => false])

@php
    $colorCSS = match ($color) {
        'orange' => 'text-tint hover:text-tint-800 active:text-tint',
        default => '',
    }
@endphp

<a {{ $attributes->merge(['class' => 'inline-flex items-center justify-center pl-2 pr-2 pt-1 pb-1 w-8 h-8 bg-secondary border border-transparent rounded-full text-xs font-semibold uppercase tracking-widest shadow-md transition ease-in-out duration-150 hover:bg-tertiary active:bg-secondary active:border-tint active:ring-tint disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default ' . $colorCSS ]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
