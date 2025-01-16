@props(['disabled' => false])

    <input {{ $attributes->merge(['class' => 'form-input bg-secondary text-primary rounded-md shadow-sm']) }} {{ $disabled ? 'disabled' : '' }}>
