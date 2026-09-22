@props(['isRow' => true])

@php
    $class = $isRow ? 'w-[98%] max-w-sm sm:w-80 pb-2 shrink-0 snap-normal snap-start' : 'w-full sm:w-80';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow ' . $class]) }}>
    <div class="flex flex-nowrap gap-2 items-center justify-between">
        <p class="shrink-0 w-16 aspect-square bg-secondary rounded-full"></p>

        <div class="flex flex-col w-full gap-1">
            <p class="bg-secondary rounded-md" style="width: 60%; height: 20px"></p>
            <p class="bg-secondary rounded-md" style="width: 80%; height: 16px"></p>
        </div>

        <p class="shrink-0 w-24 bg-secondary rounded-full" style="height: 32px"></p>
    </div>
</div>
