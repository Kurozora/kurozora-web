@props(['isRow' => true])

@php
    $class = $isRow ? 'lockup-media' : 'lockup-media-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <p class="w-full aspect-video bg-secondary rounded-lg"></p>

    <div class="flex flex-nowrap gap-2 mt-4">
        <p class="shrink-0 w-28 h-40 bg-secondary rounded-lg"></p>

        <div class="flex flex-col w-full gap-2 justify-between">
            <div class="flex flex-col gap-1">
                <p class="bg-secondary rounded-md" style="width: 100%; height: 20px"></p>
                <p class="bg-secondary rounded-md" style="width: 75%; height: 16px"></p>
            </div>

            <p class="w-24 bg-secondary rounded-full sm:w-32" style="height: 32px"></p>
        </div>
    </div>

    <p class="bg-secondary rounded-md mt-4" style="width: 60%; height: 16px"></p>
</div>
