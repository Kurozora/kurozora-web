@props(['disabled' => false])

<input {{ $attributes->merge(['class' => 'form-input rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500']) }} {{ $disabled ? 'disabled' : '' }}>
