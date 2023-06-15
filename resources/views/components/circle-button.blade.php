@props(['color' => null, 'disabled' => false])

@php
    $colorCSS = match ($color) {
        'orange' => 'text-orange-500 hover:text-orange-400 active:text-orange-500',
        'red' => 'text-red-500 hover:text-red-600 active:text-red-500',
        'yellow' => 'text-yellow-300 hover:text-yellow-500 active:text-yellow-300',
        default => '',
    }
@endphp

<button {{ $attributes->merge(['class' => 'inline-flex items-center justify-center pl-2 pr-2 pt-1 pb-1 w-8 h-8 bg-white border border-transparent rounded-full text-xs font-semibold uppercase tracking-widest shadow-md transition ease-in-out duration-150 hover:bg-gray-100 active:bg-white focus:outline-none active:border-orange-600 active:ring-orange disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default ' . $colorCSS ]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
