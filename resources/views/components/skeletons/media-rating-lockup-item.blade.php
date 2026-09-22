@props(['isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-[98%] sm:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap gap-2">
        <p class="shrink-0 w-28 h-40 bg-secondary rounded-lg"></p>

        <div class="flex flex-col w-full gap-2">
            <div class="flex gap-2 justify-between">
                <p class="bg-secondary rounded-md" style="width: 50%; height: 18px"></p>
                <p class="bg-secondary rounded-md" style="width: 30%; height: 18px"></p>
            </div>

            <p class="bg-secondary rounded-md" style="width: 96px; height: 20px"></p>

            <div class="flex flex-col gap-1">
                <p class="bg-secondary rounded-md" style="width: 100%; height: 16px"></p>
                <p class="bg-secondary rounded-md" style="width: 75%; height: 16px"></p>
            </div>
        </div>
    </div>
</div>
