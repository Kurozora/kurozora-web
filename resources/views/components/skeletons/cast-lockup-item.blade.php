@props(['isRow' => true])

@php
    $class = $isRow ? 'lockup-cast' : 'lockup-cast-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
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
