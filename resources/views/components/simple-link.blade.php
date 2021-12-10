@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center text-xs text-orange-500 tracking-widest focus:outline-none hover:text-orange-400 hover:underline active:text-orange-600 disabled:text-gray-300 disabled:cursor-default transition ease-in-out duration-150']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
