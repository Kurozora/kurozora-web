@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-secondary']) }}>
    {{ $value ?? $slot }}
</label>
