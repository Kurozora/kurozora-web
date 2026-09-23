@props(['recap', 'title', 'subtitle', 'currentModel', 'currentDetail' => null, 'previousModel', 'previousDetail' => null])

@once
    <style>
        .recap-comparison-gradient {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            filter: blur(32px);
            pointer-events: none;
        }

        .recap-comparison-gradient > div {
            position: absolute;
            top: -10%;
            left: -19%;
            width: 100%;
            height: auto;
            aspect-ratio: 1.519 / 1;
            background-image: linear-gradient(35deg, var(--recap-gradient-color1) 30%, var(--recap-gradient-color2) 68%);
            clip-path: url(#recap-gradient-shape-f);
            opacity: 0.8;
        }

        .recap-comparison-gradient--current {
            transform: translateY(-20px);
        }

        .recap-comparison-gradient--previous {
            transform: scaleX(-1);
        }

        .recap-comparison-gradient--previous > div {
            width: 122%;
            aspect-ratio: 2.019 / 1;
            background-image: linear-gradient(229deg, var(--recap-gradient-color1) 30%, var(--recap-gradient-color2) 68%);
        }
    </style>
@endonce

<x-recap-gradient-shapes />

@php
    $entries = [
        ['model' => $currentModel, 'detail' => $currentDetail, 'year' => $recap->year],
        ['model' => $previousModel, 'detail' => $previousDetail, 'year' => $recap->year - 1],
    ];
@endphp

<div
    {{ $attributes->merge(['class' => 'relative flex flex-col w-full shrink-0 bg-blur backdrop-blur rounded-xl shadow-md overflow-hidden snap-start', 'style' => 'min-width: 256px; max-width: 384px; --recap-gradient-color1: ' . $recap->background_color1 . '; --recap-gradient-color2: ' . $recap->background_color2 . ';']) }}
>
    <div class="recap-comparison-gradient recap-comparison-gradient--current" aria-hidden="true">
        <div></div>
    </div>

    <div class="relative pt-4 pl-4 pr-4">
        <h3 class="text-2xl font-semibold">{{ $title }}</h3>
        <p class="text-2xl text-secondary font-semibold">{{ $subtitle }}</p>
    </div>

    @foreach ($entries as $index => $entry)
        @php($model = $entry['model'])

        @if ($index === 1)
            <div class="relative h-px ml-4 secondary-separator-color" aria-hidden="true"></div>
        @endif

        <a
            @class([
                'relative flex items-center gap-4 pl-4 pr-4 overflow-hidden',
                'flex-row justify-between pt-8 pb-6' => $index === 0,
                'flex-row-reverse justify-end pt-6 pb-8' => $index === 1,
            ])
            href="{{ match (true) {
                $model instanceof \App\Models\Manga => route('manga.details', $model),
                $model instanceof \App\Models\Game => route('games.details', $model),
                default => route('anime.details', $model),
            } }}"
            wire:navigate
        >
            @if ($index === 1)
                <div class="recap-comparison-gradient recap-comparison-gradient--previous" aria-hidden="true">
                    <div></div>
                </div>
            @endif

            <div class="relative min-w-0">
                <p class="text-sm text-secondary font-semibold">{{ $entry['year'] }}</p>
                <p class="mt-1 text-lg font-semibold leading-tight line-clamp-2">{{ $model->title }}</p>

                @if (!empty($entry['detail']))
                    <p class="mt-1 text-lg text-secondary leading-tight line-clamp-2">{{ $entry['detail'] }}</p>
                @endif
            </div>

            <picture
                class="relative shrink-0 h-28 rounded-lg overflow-hidden"
                style="aspect-ratio: {{ $model instanceof \App\Models\Game ? '1 / 1' : '3 / 4.23' }}; background-color: {{ $model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $model->title }} Poster" title="{{ $model->title }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        </a>
    @endforeach

    <div class="absolute top-0 left-0 h-full w-full border border-solid border-primary rounded-xl pointer-events-none"></div>
</div>
