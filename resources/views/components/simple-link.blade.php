@props(['disabled' => false, 'hoverUnderlineEnabled' => true])

<a {{ $attributes->merge(['class' => 'inline-flex items-center text-tint transition ease-in-out duration-150 hover:text-tint ' . ($hoverUnderlineEnabled ? 'hover:underline' : '') . ' active:text-tint disabled:text-gray-300 disabled:cursor-default disabled:text-gray-400 disabled:cursor-default']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</a>
