@props(['disabled' => false])

<x-simple-link {{ $attributes->merge(['class' => 'text-xs tracking-widest']) }} :disabled="$disabled">
    {{ $slot }}
</x-simple-link>
