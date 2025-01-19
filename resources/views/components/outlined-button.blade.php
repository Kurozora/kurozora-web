@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center pl-2 pr-2 pt-1 pb-1 bg-white border border-tint rounded-md font-semibold text-xs text-tint uppercase tracking-widest shadow-sm transition ease-in-out duration-150 hover:bg-tint-800 hover:text-white focus:border-tint focus:ring-tint active:text-white active:bg-tint disabled:bg-gray-200 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default sm:px-4 sm:py-2']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
