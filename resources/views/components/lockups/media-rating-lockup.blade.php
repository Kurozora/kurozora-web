@props(['mediaRating', 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    @switch($mediaRating->model_type)
        @case(\App\Models\Anime::class)
            <div class="flex flex-nowrap gap-2">
                <picture class="relative shrink-0 w-28 h-40 rounded-lg overflow-hidden">
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $mediaRating->model->title }} Poster" title="{{ $mediaRating->model->title }}">

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('anime.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col w-full gap-2">
                    <div class="flex flex-row justify-between">
                        <p class="leading-tight line-clamp-2">{{ $mediaRating->model->title }}</p>

                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <x-truncated-text>
                        <x-slot:text>
                            {!! nl2br(e($mediaRating->description)) !!}
                        </x-slot:text>
                    </x-truncated-text>
                </div>
            </div>
            @break
        @case(\App\Models\Game::class)
            <div class="flex flex-nowrap gap-2">
                <picture class="relative shrink-0 w-28 h-28 rounded-3xl overflow-hidden">
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $mediaRating->model->title }} Poster" title="{{ $mediaRating->model->title }}">

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-3xl"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('games.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col w-full gap-2">
                    <div class="flex flex-row justify-between">
                        <p class="leading-tight line-clamp-2">{{ $mediaRating->model->title }}</p>

                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <x-truncated-text>
                        <x-slot:text>
                            {!! nl2br(e($mediaRating->description)) !!}
                        </x-slot:text>
                    </x-truncated-text>
                </div>
            </div>
            @break
        @case(\App\Models\Manga::class)
            <div class="flex flex-nowrap gap-2">
                <svg class="relative shrink-0 w-28 h-40 overflow-hidden">
                    <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">
                        <img class="h-full w-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $mediaRating->model->title }} Poster" title="{{ $mediaRating->model->title }}" />
                    </foreignObject>

                    <g opacity="0.40">
                        <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                        <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                        <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
                    </g>
                </svg>

                <a class="absolute w-full h-full" href="{{ route('manga.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col w-full gap-2">
                    <div class="flex flex-row justify-between">
                            <p class="leading-tight line-clamp-2">{{ $mediaRating->model->title }}</p>

                            <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <x-truncated-text>
                        <x-slot:text>
                            {!! nl2br(e($mediaRating->description)) !!}
                        </x-slot:text>
                    </x-truncated-text>
                </div>
            </div>
        @break
        @default
            not supported yet
    @endswitch
</div>
