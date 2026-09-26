@props(['isRow' => true])

@php
    $class = $isRow ? 'lockup-music' : 'lockup-music-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <p class="w-full aspect-square bg-secondary rounded-lg"></p>

    <div class="flex flex-col gap-1 mt-2">
        <p class="bg-secondary rounded-md" style="width: 100%; height: 20px"></p>
        <p class="bg-secondary rounded-md" style="width: 60%; height: 18px"></p>
    </div>
</div>
