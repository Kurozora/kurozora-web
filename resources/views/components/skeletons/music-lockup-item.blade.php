@props(['isRow' => true])

@php
    $class = $isRow ? 'max-w-[16rem] shrink-0' : 'flex-grow';
@endphp

<div {{ $attributes->merge(['class' => 'relative pb-2 w-[98%] sm:w-64 snap-normal snap-start ' . $class]) }}>
    <p class="w-full aspect-square bg-secondary rounded-lg"></p>

    <div class="flex flex-col gap-1 mt-2">
        <p class="bg-secondary rounded-md" style="width: 100%; height: 20px"></p>
        <p class="bg-secondary rounded-md" style="width: 60%; height: 18px"></p>
    </div>
</div>
