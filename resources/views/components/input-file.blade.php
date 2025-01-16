@props(['disabled' => false])

<input type="file" {{ $attributes->merge(['class' => 'block w-full text-sm text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-secondary file:text-tint hover:file:bg-tertiary']) }} {{ $disabled ? 'disabled' : '' }} />
