@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center pt-2 pr-2 pb-2 pl-2 rounded-md text-secondary hover:text-primary hover:bg-tertiary focus:bg-secondary focus:text-secondary transition duration-150 ease-in-out']) }} {{ $disabled ? 'disabled' : '' }}>
    {{ $slot }}
</button>
