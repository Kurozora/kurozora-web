@props(['isRow' => true])

@php
    $class = $isRow ? 'w-[98%] max-w-sm shrink-0 snap-normal snap-start' : 'w-[98%] sm:w-96 flex-grow';
@endphp

<div {{ $attributes->merge(['class' => 'relative pb-2 ' . $class]) }}>
    <div class="flex flex-nowrap gap-2">
        <p class="shrink-0 w-28 h-40 bg-secondary rounded-lg"></p>

        <div class="flex flex-col flex-grow w-full gap-2">
            <div class="flex flex-col gap-1">
                <p class="bg-secondary rounded-md" style="width: 100%; height: 18px"></p>
                <p class="bg-secondary rounded-md" style="width: 60%; height: 14px"></p>
            </div>

            <div class="flex flex-col items-end gap-1">
                <p class="bg-secondary rounded-md" style="width: 100%; height: 18px"></p>
                <p class="bg-secondary rounded-md" style="width: 60%; height: 14px"></p>
            </div>
        </div>

        <p class="shrink-0 w-28 h-40 bg-secondary rounded-lg"></p>
    </div>
</div>
