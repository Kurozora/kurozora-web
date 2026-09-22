@props(['isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col flex-grow w-[98%] sm:w-80 ' . $class]) }}>
    <div class="relative flex items-center justify-center w-full aspect-video bg-secondary rounded-lg">
        <p class="h-32 aspect-square bg-tertiary rounded-full"></p>
    </div>

    <div class="flex flex-col gap-1 mt-2">
        <p class="bg-secondary rounded-md" style="width: 60%; height: 20px"></p>
        <p class="bg-secondary rounded-md" style="width: 40%; height: 16px"></p>
    </div>
</div>
