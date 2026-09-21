@props(['position' => 'absolute', 'edge' => 'top'])

@php
    $position = match($position) {
        'fixed' => 'fixed',
        default => 'absolute'
    };
    $edgePosition = match($edge) {
        'bottom' => 'bottom-0',
        default => 'top-0'
    };
    $edgeDirection = match($edge) {
        'bottom' => 'edge-blur-bottom',
        default => 'edge-blur-top'
    };
    $gradient = match($edge) {
        'bottom' => 'from-transparent to-[var(--bg-blur-color)]',
        default => 'from-[var(--bg-blur-color)] to-transparent'
    };
@endphp

<div {{ $attributes->merge(['style' => 'height: 130px;', 'class' => $position . ' ' . $edgePosition . ' ' . $edgeDirection . ' left-0 w-full pointer-events-none overflow-hidden']) }}>
    <div class="edge-blur-layer"></div>

    <div class="edge-blur-layer"></div>

    <div class="edge-blur-layer"></div>

    <div class="absolute inset-x-0 {{ $edgePosition }} h-full bg-gradient-to-b {{ $gradient }}"></div>
</div>
