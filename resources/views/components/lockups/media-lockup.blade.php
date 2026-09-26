@props([
    'model',
    'kind',
    'href',
    'scheduleDate' => null,
    'scheduleDuration' => 25,
    'scheduleTitle' => null,
    'clamp' => 2,
    'relation' => null,
    'rank' => null,
    'eyebrow' => null,
    'detail' => null,
    'favoriteStatus' => null,
    'trackingEnabled' => true,
    'showsSchedule' => false,
    'isRanked' => false,
    'isRow' => true,
    'inLibrary' => false,
])

@php
    $class = $isRow ? 'lockup-media' : 'lockup-media-grid';
    $lineClamp = $clamp === 1 ? 'line-clamp-1' : 'line-clamp-2';
    $subtitle = empty($model->tagline)
        ? ($model->genres?->pluck('name')->join(', ', ' and ') ?? $model->themes?->pluck('name')->join(', ', ' and '))
        : $model->tagline;
@endphp

<div {{ $attributes->merge(['class' => $class]) }} @if ($inLibrary) data-in-library @endif>
    <div class="flex flex-nowrap gap-2">
        @if ($favoriteStatus !== null)
            <div class="flex items-center shrink-0 w-3 -ml-4 -mr-1 text-tint" @if ($favoriteStatus) title="{{ __('Favorited') }}" @endif>
                @if ($favoriteStatus)
                    @svg('heart_fill', 'fill-current', ['width' => 12])
                @endif
            </div>
        @endif

        <x-lockups.media-poster :model="$model" :kind="$kind" />

        <a class="absolute w-full h-full" href="{{ $href }}" wire:navigate></a>

        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
                @if (!empty($eyebrow))
                    <p class="text-sm leading-tight font-semibold uppercase opacity-75">{{ $eyebrow }}</p>
                @elseif ($isRanked)
                    <p class="text-sm leading-tight font-semibold" title="{{ __('Ranked #:x', ['x' => $rank]) }}">{{ __('#:x', ['x' => $rank]) }}</p>
                @endif

                @if (!empty($relation))
                    <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-2" title="{{ $relation->name }}">{{ $relation->name }}</p>
                @endif

                @if ($showsSchedule)
                    <p
                        class="text-xs leading-tight font-semibold opacity-75 line-clamp-2"
                        @if (!empty($scheduleTitle)) x-bind:title="'{{ $scheduleTitle }} ' + scheduleString" @endif
                        x-data="{
                            scheduleTimestamp: {{ $scheduleDate?->timestamp }},
                            scheduleDuration: {{ $scheduleDuration }},
                            scheduleString: '',
                            startTimer() {
                                if (this.scheduleTimestamp == null) {
                                    return;
                                }

                                this.scheduleString = '(' + Date.broadcastString(this.scheduleTimestamp * 1000, this.scheduleDuration) + ')'
                            },
                        }"
                        x-init="() => {
                            setInterval(() => {
                                startTimer()
                            }, 1000);
                        }"
                    >
                        {{ $scheduleDate?->format('H:i T') }}
                        <span class="font-normal" x-text="scheduleString"></span>
                    </p>
                @endif

                <p class="leading-tight {{ $lineClamp }}" title="{{ $model->title }}">{{ $model->title }}</p>

                @if (!empty($detail))
                    <p class="text-xs leading-tight font-semibold opacity-75 line-clamp-1">{{ $detail }}</p>
                @endif

                <div class="flex flex-col gap-1">
                    <p class="text-xs leading-tight opacity-75 {{ $lineClamp }}" title="{{ $subtitle }}">{{ $subtitle }}</p>
                    <p class="text-xs leading-tight opacity-75 {{ $lineClamp }}" title="{{ $model->tvRating->name }}">{{ $model->tvRating->name }}</p>
                </div>

                @if (!empty($model->mediaStat?->rating_count) && $trackingEnabled)
                    <div class="inline-flex items-center gap-1 my-auto">
                        <p class="text-sm font-bold text-tint">{{ number_format($model->mediaStat?->rating_average ?? 0, 1) }}</p>

                        <livewire:components.star-rating :rating="$model->mediaStat?->rating_average" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                    </div>
                @endif
            </div>

            @if ($trackingEnabled)
                <livewire:components.library-button :model="$model" wire:key="{{ uniqid($model->id, true) }}" />
            @endif
        </div>
    </div>
</div>
