@props(['disabled' => false])

<input {{ $attributes->merge(['class' => 'form-input bg-secondary text-primary border-primary rounded-md shadow-sm focus:border-tint focus:ring-tint']) }} {{ $disabled ? 'disabled' : '' }}>
