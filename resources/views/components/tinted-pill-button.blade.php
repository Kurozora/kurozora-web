@props(['color' => null, 'disabled' => false])

@php
    $colorCSS = match ($color) {
        'orange' => 'bg-tint btn-text-tinted hover:bg-tint-800 active:bg-tint active:border-tint active:ring-tint',
        'transparent' => '',
        'glass' => 'bg-blur backdrop-blur text-primary hover:bg-tertiary active:bg-blur active:border-tint active:ring-tint',
        default => 'bg-secondary hover:bg-tertiary active:bg-secondary active:border-tint active:ring-tint',
    };
@endphp

<button {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-1 pl-2 pr-2 pt-1 pb-1 h-8 border border-transparent text-xs font-semibold uppercase tracking-widest shadow-md rounded-full transition ease-in-out duration-150 disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default ' . $colorCSS ]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
