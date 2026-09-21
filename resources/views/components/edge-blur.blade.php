@props(['position' => 'absolute', 'edge' => 'top', 'tint' => true, 'height' => '130px', 'plateau' => '0px'])

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

<div {{ $attributes->merge(['style' => 'height: ' . $height . '; --edge-blur-plateau: ' . $plateau . ';', 'class' => 'edge-blur ' . $position . ' ' . $edgePosition . ' ' . $edgeDirection . ' left-0 w-full pointer-events-none overflow-hidden']) }}>
    <div class="edge-blur-layer"></div>

    <div class="edge-blur-layer"></div>

    <div class="edge-blur-layer"></div>

    @if ($tint)
        <div class="edge-blur-tint absolute inset-x-0 {{ $edgePosition }} h-full bg-gradient-to-b {{ $gradient }}"></div>
    @endif
</div>
