@props(['isRow' => true])

@php
    $class = $isRow ? 'lockup-wide' : 'lockup-wide-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <p class="w-full aspect-video bg-secondary rounded-lg"></p>

    <div class="flex flex-col w-full gap-2 justify-between mt-2">
        <div class="flex flex-col gap-1">
            <p class="bg-secondary rounded-md" style="width: 40%; height: 14px"></p>
            <p class="bg-secondary rounded-md" style="width: 100%; height: 20px"></p>
            <p class="bg-secondary rounded-md" style="width: 60%; height: 14px"></p>
            <p class="bg-secondary rounded-md" style="width: 75%; height: 14px"></p>
        </div>

        <p class="w-24 bg-secondary rounded-full sm:w-32" style="height: 32px"></p>
    </div>
</div>
