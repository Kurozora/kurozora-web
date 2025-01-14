@props(['active', 'disabled' => false])

@php
    $classes = ($active ?? false)
                ? 'block pl-3 pr-4 pt-2 pb-2 text-base font-medium bg-tint btn-text-tinted rounded-lg transition duration-150 ease-in-out'
                : 'block pl-3 pr-4 pt-2 pb-2 text-base font-medium text-secondary rounded-lg transition duration-150 ease-in-out hover:bg-tint hover:text-primary';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
