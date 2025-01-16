@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center justify-center pl-2 pr-2 pt-1 pb-1 bg-tint border border-transparent rounded-md text-xs btn-text-tinted font-semibold uppercase tracking-widest transition ease-in-out duration-150 hover:bg-orange-400 active:bg-tint focus:outline-none active:border-orange-600 active:ring-orange disabled:bg-gray-200 disabled:border-gray-300 disabled:text-gray-400 disabled:cursor-default sm:px-4 sm:py-2']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
