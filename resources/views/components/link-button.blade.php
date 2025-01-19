@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center justify-center pl-2 pr-2 pt-1 pb-1 bg-tint border border-transparent rounded-md text-xs btn-text-tinted font-semibold uppercase tracking-widest transition ease-in-out duration-150 hover:bg-tint-800 active:bg-tint active:border-tint active:ring-tint disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default sm:px-4 sm:py-2']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
