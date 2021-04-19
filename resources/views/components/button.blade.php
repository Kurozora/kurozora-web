@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-400 active:bg-orange-600 focus:outline-none focus:border-orange-600 focus:ring-orange disabled:bg-gray-200 disabled:border-gray-200 disabled:text-gray-300 disabled:cursor-default transition ease-in-out duration-150']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
