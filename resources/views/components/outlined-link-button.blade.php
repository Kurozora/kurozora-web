@props(['disabled' => false, 'color' => 'orange'])

@php
    $colorCSS = match ($color) {
        'amber' => 'text-amber-500 border-amber-500 hover:bg-amber-400 hover:border-amber-400 focus:border-amber-600 focus:ring-amber active:bg-amber-600',
        'blue' => 'text-blue-500 border-blue-500 hover:bg-blue-400 hover:border-blue-400 focus:border-blue-600 focus:ring-blue active:bg-blue-600',
        'green' => 'text-green-500 border-green-500 hover:bg-green-400 hover:border-green-400 focus:border-green-600 focus:ring-green active:bg-green-600',
        'pink' => 'text-pink-500 border-pink-500 hover:bg-pink-400 hover:border-pink-400 focus:border-pink-600 focus:ring-pink active:bg-pink-600',
        'violet' => 'text-violet-500 border-violet-500 hover:bg-violet-400 hover:border-violet-400 focus:border-violet-600 focus:ring-violet active:bg-violet-600',
        default => 'text-tint border-tint hover:bg-tint-800 focus:border-tint focus:ring-tint active:bg-tint'
    };
@endphp

<a {{ $attributes->merge(['class' => 'inline-flex items-center pl-4 pr-4 pt-2 pb-2 bg-white border rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:btn-text-tinted focus:outline-none active:btn-text-tinted disabled:bg-gray-200 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default transition ease-in-out duration-150 ' . $colorCSS]) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
