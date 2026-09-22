@props(['isRow' => true])

@php
    $class = $isRow ? 'shrink-0 snap-normal snap-center' : 'flex-grow';
@endphp

<div {{ $attributes->merge(['class' => 'w-[98%] sm:w-80 pb-2 ' . $class]) }}>
    <p class="w-full aspect-video bg-secondary rounded-xl"></p>

    <div class="flex flex-nowrap items-start gap-2 mt-3">
        <div class="flex flex-col flex-grow w-full gap-1">
            <p class="bg-secondary rounded-md" style="width: 100%; height: 18px"></p>
            <p class="bg-secondary rounded-md" style="width: 60%; height: 14px"></p>
        </div>

        <p class="shrink-0 w-24 bg-secondary rounded-full" style="height: 32px"></p>
    </div>
</div>
