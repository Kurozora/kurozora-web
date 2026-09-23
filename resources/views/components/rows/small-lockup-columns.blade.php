@props(['models', 'details' => [], 'eyebrows' => [], 'favoritedIDs' => [], 'isRanked' => true])

<x-rows.container {{ $attributes }}>
    @foreach ($models->values()->chunk(2) as $column)
        <div class="flex flex-col gap-2 shrink-0 w-[98%] max-w-sm sm:w-80 snap-normal snap-start">
            @foreach ($column as $index => $model)
                @if (!$loop->first)
                    <div class="flex flex-nowrap gap-2" aria-hidden="true">
                        <div class="shrink-0 w-3 -ml-4 -mr-1"></div>
                        <div class="shrink-0 w-28"></div>
                        <div class="flex-grow h-px secondary-separator-color"></div>
                    </div>
                @endif

                @switch(true)
                    @case($model instanceof \App\Models\Manga)
                        <x-lockups.small-lockup class="w-full" :manga="$model" :rank="$index + 1" :eyebrow="$eyebrows[$index] ?? null" :detail="$details[$model->id] ?? null" :favorite-status="in_array($model->id, $favoritedIDs)" :tracking-enabled="false" :is-ranked="$isRanked" :is-row="false" />
                        @break
                    @case($model instanceof \App\Models\Game)
                        <x-lockups.small-lockup class="w-full" :game="$model" :rank="$index + 1" :eyebrow="$eyebrows[$index] ?? null" :detail="$details[$model->id] ?? null" :favorite-status="in_array($model->id, $favoritedIDs)" :tracking-enabled="false" :is-ranked="$isRanked" :is-row="false" />
                        @break
                    @default
                        <x-lockups.small-lockup class="w-full" :anime="$model" :rank="$index + 1" :eyebrow="$eyebrows[$index] ?? null" :detail="$details[$model->id] ?? null" :favorite-status="in_array($model->id, $favoritedIDs)" :tracking-enabled="false" :is-ranked="$isRanked" :is-row="false" />
                @endswitch
            @endforeach
        </div>
    @endforeach
</x-rows.container>
