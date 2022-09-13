@props(['color' => null, 'disabled' => false])

@php
    $colorCSS = match ($color) {
        'orange' => 'bg-orange-500 text-white hover:bg-orange-400 active:bg-orange-500',
        default => 'bg-white hover:bg-gray-100 active:bg-white',
    }
@endphp

<button {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-1 px-2 pt-1 pb-1 h-8 border border-transparent rounded-full text-xs font-semibold uppercase tracking-widest shadow-md transition ease-in-out duration-150  focus:outline-none active:border-orange-600 active:ring-orange disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default sm:h-10 ' . $colorCSS ]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
