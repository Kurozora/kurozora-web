@props(['isRow' => true])

@php
    $class = $isRow ? 'max-w-sm pb-2 shrink-0 snap-normal snap-start' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-[98%] sm:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap gap-2">
        <p class="shrink-0 w-28 h-40 bg-secondary rounded-lg"></p>

        <div class="flex flex-col w-full gap-2 justify-between">
            <p class="bg-secondary rounded-md" style="width: 75%; height: 20px"></p>

            <div class="flex flex-col gap-2">
                @for ($index = 0; $index < 3; $index++)
                    <div class="flex w-full justify-between">
                        <p class="bg-secondary rounded-md" style="width: 40%; height: 16px"></p>
                        <p class="bg-secondary rounded-md" style="width: 30%; height: 16px"></p>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
