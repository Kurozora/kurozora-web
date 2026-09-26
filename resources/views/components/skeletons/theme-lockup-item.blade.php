<div {{ $attributes->merge(['class' => 'relative flex-grow w-[98%] sm:w-80']) }}>
    <div class="flex items-end gap-2 pt-4 pb-4">
        <div class="flex flex-col w-full gap-1">
            <p class="bg-secondary rounded-md" style="width: 60%; height: 20px"></p>
            <p class="bg-secondary rounded-md" style="width: 80%; height: 16px"></p>
        </div>

        <p class="shrink-0 w-24 bg-secondary rounded-full" style="height: 32px"></p>
    </div>

    <div class="flex gap-2 justify-between">
        @for ($index = 0; $index < 3; $index++)
            <p class="w-1/3 bg-secondary rounded-lg" style="aspect-ratio: 9 / 19.5;"></p>
        @endfor
    </div>
</div>
