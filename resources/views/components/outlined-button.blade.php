@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-2 py-1 bg-white border border-orange-500 rounded-md font-semibold text-xs text-orange-500 uppercase tracking-widest shadow-sm transition ease-in-out duration-150 hover:bg-orange-400 hover:border-orange-400 hover:text-white focus:outline-none focus:border-orange-600 focus:ring-orange active:text-white active:bg-orange-600 disabled:bg-gray-200 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default sm:px-4 sm:py-2']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
