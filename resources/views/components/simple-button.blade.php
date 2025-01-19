@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center text-xs text-tint tracking-widest transition ease-in-out duration-150 focus:outline-none hover:text-tint-800 hover:underline active:text-tint disabled:text-gray-300 disabled:cursor-default disabled:text-gray-400 disabled:cursor-default']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
