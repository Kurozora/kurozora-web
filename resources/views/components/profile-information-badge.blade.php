@props(['disabled' => false])

<a {{ $attributes->merge(['class' => 'w-48 text-center']) }} {{ $disabled ? 'disabled' : '' }}>
    <p class="font-semibold">{{ $description }}</p>
    <p class="text-gray-500 text-sm font-semibold">{{ $title }}</p>
</a>
