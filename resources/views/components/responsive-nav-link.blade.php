@props(['active', 'disabled' => false])

@php
    $classes = ($active ?? false)
                ? 'block pl-3 pr-4 pt-2 pb-2 text-base font-medium text-orange-700 bg-orange-50 focus:outline-none focus:text-orange-800 focus:bg-orange-100 rounded-lg transition duration-150 ease-in-out'
                : 'block pl-3 pr-4 pt-2 pb-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:text-gray-800 focus:bg-gray-50 rounded-lg transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
