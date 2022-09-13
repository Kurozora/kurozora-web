@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center pl-4 pr-4 pt-2 pb-2 bg-white border border-orange-500 rounded-md font-semibold text-xs text-orange-500 uppercase tracking-widest shadow-sm hover:bg-orange-400 hover:border-orange-400 hover:text-white focus:outline-none focus:border-orange-600 focus:ring-orange active:text-white active:bg-orange-600 disabled:bg-gray-200 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default transition ease-in-out duration-150']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
