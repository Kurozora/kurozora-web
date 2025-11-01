@props(['position' => 'absolute', 'edge' => 'top'])

@php
    $position = match($position) {
        'fixed' => 'fixed',
        default => 'absolute'
    };
    $edge = match($edge) {
        'bottom' => 'bottom-0',
        default => 'top-0'
    };
    $mask1Direction = match($edge) {
        'bottom' => 'mask-b-from-100% mask-b-to-70%',
        default => 'mask-b-from-70% mask-b-to-100%'
    };
    $mask2Direction = match($edge) {
        'bottom' => 'mask-b-from-75% mask-b-to-50%',
        default => 'mask-b-from-50% mask-b-to-75%'
    };
    $mask3Direction = match($edge) {
        'bottom' => 'mask-b-from-55% mask-b-to-20%',
        default => 'mask-b-from-20% mask-b-to-55%'
    };
    $gradient = match($edge) {
        'bottom' => 'from-transparent to-[var(--bg-blur-color)]',
        default => 'from-[var(--bg-blur-color)] to-transparent'
    };
@endphp

<div {{ $attributes->merge(['style' => 'height: 130px;']) }} class="{{ $position }} {{ $edge }} left-0 w-full pointer-events-none overflow-hidden">
    <div class="absolute {{ $edge }} left-0 w-full h-full {{ $mask1Direction }}" style="--tw-backdrop-blur: blur(0.8px); backdrop-filter: blur(0.5px);"></div>

    <div class="absolute {{ $edge }} left-0 w-full h-full {{ $mask2Direction }}" style="--tw-backdrop-blur: blur(2px); backdrop-filter: blur(1px);"></div>

    <div class="absolute {{ $edge }} left-0 w-full h-full {{ $mask3Direction }}" style="--tw-backdrop-blur: blur(4px); backdrop-filter: blur(1px);"></div>

    <div class="pointer-events-none absolute inset-x-0 {{ $edge }} h-full bg-gradient-to-b {{ $gradient }}" style="opacity: 1;"></div>
</div>
