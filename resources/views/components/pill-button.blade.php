@props(['color' => null, 'disabled' => false])

@php
    $colorCSS = match ($color) {
        'orange' => 'text-orange-500 hover:text-orange-400 active:text-orange-500',
        default => '',
    }
@endphp

<button {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-1 px-2 pt-1 pb-1 h-8 bg-white border border-transparent rounded-full text-xs font-semibold uppercase tracking-widest shadow-md transition ease-in-out duration-150 hover:bg-gray-100 active:bg-white focus:outline-none active:border-orange-600 active:ring-orange disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default sm:h-10 ' . $colorCSS ]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
