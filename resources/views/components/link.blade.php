@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'inline-flex items-center text-orange-700 hover:text-orange-500 font-medium underline transition ease-in-out duration-150 disabled:opacity-25']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
