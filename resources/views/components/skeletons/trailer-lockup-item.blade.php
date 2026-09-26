@props(['isRow' => true])

@php
    $class = $isRow ? 'lockup-trailer' : 'lockup-trailer-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <p class="w-full aspect-video bg-secondary rounded-xl"></p>

    <div class="flex flex-nowrap items-start gap-2 mt-3">
        <div class="flex flex-col flex-grow w-full gap-1">
            <p class="bg-secondary rounded-md" style="width: 100%; height: 18px"></p>
            <p class="bg-secondary rounded-md" style="width: 60%; height: 14px"></p>
        </div>

        <p class="shrink-0 w-24 bg-secondary rounded-full" style="height: 32px"></p>
    </div>
</div>
