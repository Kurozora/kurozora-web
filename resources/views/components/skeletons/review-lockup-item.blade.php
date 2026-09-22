@props(['isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-[98%] sm:w-96 ' . $class]) }}>
    <div class="flex flex-row gap-2 pr-2 pl-2 pt-2 pb-2 h-full bg-secondary rounded-xl">
        <p class="shrink-0 w-12 h-12 bg-tertiary rounded-full"></p>

        <div class="flex flex-col w-full gap-2">
            <div class="flex justify-between">
                <p class="bg-tertiary rounded-md" style="width: 40%; height: 18px"></p>
                <p class="bg-tertiary rounded-md" style="width: 30%; height: 18px"></p>
            </div>

            <p class="bg-tertiary rounded-md" style="width: 50%; height: 16px"></p>

            <div class="flex flex-col gap-1">
                <p class="bg-tertiary rounded-md" style="width: 100%; height: 16px"></p>
                <p class="bg-tertiary rounded-md" style="width: 90%; height: 16px"></p>
                <p class="bg-tertiary rounded-md" style="width: 60%; height: 16px"></p>
            </div>

            <div class="flex gap-2">
                <p class="bg-tertiary rounded-md" style="width: 56px; height: 24px"></p>
                <p class="bg-tertiary rounded-md" style="width: 56px; height: 24px"></p>
            </div>
        </div>
    </div>
</div>
