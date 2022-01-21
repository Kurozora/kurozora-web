@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-2 py-1 bg-orange-500 border border-transparent rounded-md text-xs text-white font-semibold uppercase tracking-widest transition ease-in-out duration-150 hover:bg-orange-400 active:bg-orange-600 focus:outline-none active:border-orange-600 active:ring-orange disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default sm:px-4 sm:py-2']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
