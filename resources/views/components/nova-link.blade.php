@props(['href', 'color' => null, 'disabled' => false])

<x-circle-link
    href="{{ $href }}"
    rel="noindex nofollow"
    :color="$color"
    :disabled="$disabled"
>
    {{ $slot }}
</x-circle-link>
