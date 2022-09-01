@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-2 pt-1 pb-1 bg-red-600 border border-transparent rounded-md text-xs text-white font-semibold uppercase tracking-widest transition ease-in-out duration-150 hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring-red active:bg-red-600 disabled:bg-red-200 disabled:border-red-300 disabled:text-red-400 disabled:cursor-default sm:px-4 sm:py-2']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
