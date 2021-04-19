@props(['disabled' => false])

<input {{ $attributes->merge(['class' => 'form-input rounded-md shadow-sm']) }} {{ $disabled ? 'disabled' : '' }}>
