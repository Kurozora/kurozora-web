@props(['isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-28 ' . $class]) }}>
    <p class="w-full aspect-square bg-secondary rounded-full"></p>

    <div class="flex justify-center mt-2">
        <p class="bg-secondary rounded-md" style="width: 80%; height: 18px"></p>
    </div>
</div>
