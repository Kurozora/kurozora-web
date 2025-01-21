@props(['disabled' => false])

<x-simple-link {{ $attributes->merge(['class' => 'text-xs tracking-widest']) }} wire:navigate :disabled="$disabled">
    {{ $slot }}
</x-simple-link>
