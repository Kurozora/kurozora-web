@props(['disabled' => false, 'variant' => 'primary'])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'flex flex-col items-center justify-center pl-3 pr-3 pt-3 pb-3 bg-secondary border border-primary rounded-lg transition ease-in-out duration-150 hover:bg-tertiary disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default',
        default => 'inline-flex items-center justify-center pl-2 pr-2 pt-1 pb-1 bg-tint border border-transparent rounded-md text-xs btn-text-tinted font-semibold uppercase tracking-widest transition ease-in-out duration-150 hover:bg-tint-800 active:bg-tint active:border-tint active:ring-tint disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default sm:px-4 sm:py-2',
    };
@endphp

<a {{ $attributes->merge(['class' => $variantClasses]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
