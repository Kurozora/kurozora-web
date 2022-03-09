@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center text-orange-500 transition ease-in-out duration-150 focus:outline-none hover:text-orange-400 hover:underline active:text-orange-600 disabled:text-gray-300 disabled:cursor-default disabled:text-gray-400 disabled:cursor-default']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
