@props(['active', 'external' => false, 'disabled' => false])

@php
    $classes = ($active ?? false)
                ? 'block pl-3 pr-4 pt-2 pb-2 text-base bg-tint btn-text-tinted rounded-lg transition duration-150 ease-in-out'
                : 'block pl-3 pr-4 pt-2 pb-2 text-base text-secondary rounded-lg transition duration-150 ease-in-out hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
    <div class="flex gap-2" {{ $external ? 'target=_blank' : '' }}>
        {{ $slot }}
    </div>
</a>
