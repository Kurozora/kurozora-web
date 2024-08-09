@props(['active', 'external' => false, 'disabled' => false])

@php
    $classes = ($active ?? false)
                ? 'block pl-3 pr-4 pt-2 pb-2 text-base text-white bg-orange-500 focus:outline-none focus:text-orange-800 focus:bg-orange-100 rounded-lg transition duration-150 ease-in-out'
                : 'block pl-3 pr-4 pt-2 pb-2 text-base text-black hover:text-orange-800 hover:bg-orange-50 focus:outline-none focus:text-orange-800 focus:bg-orange-50 rounded-lg transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
    <div class="flex gap-2" {{ $external ? 'target=_blank' : '' }}>
        {{ $slot }}
    </div>
</a>
