@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center text-orange-700 font-medium underline disabled:opacity-25 transition ease-in-out duration-150']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
