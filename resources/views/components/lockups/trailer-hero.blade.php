@props(['videos'])

@php
    $videos = collect($videos)
        ->filter(fn ($video) => !empty($video->videoable))
        ->values();
    $featured = $videos->first();

    $detailsUrl = function ($title) {
        return $title instanceof \App\Models\Game
            ? route('games.details', $title)
            : route('anime.details', $title);
    };

    $bannerUrl = function ($title) {
        return $title->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner())
            ?? $title->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster())
            ?? asset('images/static/placeholders/anime_banner.webp');
    };
@endphp

@if (!empty($featured))
    <div data-trailer-hero>
        <div class="relative flex flex-wrap">
            <div class="relative w-full overflow-hidden max-h-[80vh] lg:w-3/4 lg:pl-4">
                <div class="flex flex-nowrap snap-mandatory snap-x overflow-x-scroll overflow-y-hidden no-scrollbar" data-trailer-hero-slides>
                    @foreach ($videos as $video)
                        <div
                            class="w-full shrink-0 snap-center"
                            data-trailer-hero-slide
                            data-code="{{ $video->code }}"
                            data-title="{{ $video->videoable->title }}"
                            data-meta="{{ $video->getMetaLine() }}"
                            data-url="{{ $detailsUrl($video->videoable) }}"
                        >
                            <div class="relative aspect-video overflow-hidden z-0 lg:rounded-3xl">
                                <div
                                    class="relative bg-secondary w-full h-full overflow-hidden z-10 lg:rounded-3xl"
                                    data-trailer-hero-frame
                                    data-src="https://www.youtube.com/watch?v={{ $video->code }}"
                                    data-poster="{{ $bannerUrl($video->videoable) }}"
                                    wire:ignore
                                >
                                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $bannerUrl($video->videoable) }}" alt="{{ $video->videoable->title }}" title="{{ $video->videoable->title }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="hidden absolute top-0 bottom-0 right-0 flex-col gap-2 pl-4 pr-4 lg:flex lg:w-1/4" data-trailer-hero-queue>
                @foreach ($videos as $video)
                    <button
                        class="flex flex-nowrap items-center gap-3 flex-1 overflow-hidden text-left rounded-lg"
                        type="button"
                        data-trailer-hero-item
                        data-code="{{ $video->code }}"
                        data-title="{{ $video->videoable->title }}"
                        data-meta="{{ $video->getMetaLine() }}"
                        data-url="{{ $detailsUrl($video->videoable) }}"
                    >
                        <picture class="relative shrink-0 bg-secondary h-full aspect-video rounded-md overflow-hidden">
                            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $bannerUrl($video->videoable) }}" alt="{{ $video->videoable->title }}" title="{{ $video->videoable->title }}">
                        </picture>

                        <span class="flex flex-col min-w-0">
                            <span class="text-sm font-semibold leading-tight line-clamp-2">{{ $video->videoable->title }}</span>
                            <span class="text-xs leading-tight text-secondary line-clamp-2 mt-1">{{ $video->getMetaLine() }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex flex-nowrap items-start gap-2 pl-4 pr-4 mt-3 transition duration-300" data-trailer-hero-caption>
            <div class="min-w-0 flex-grow">
                <a class="inline-block" data-trailer-hero-link href="{{ $detailsUrl($featured->videoable) }}" wire:navigate>
                    <h2 class="text-2xl font-bold leading-tight line-clamp-2" data-trailer-hero-title>{{ $featured->videoable->title }}</h2>
                </a>

                <p class="text-sm leading-tight text-secondary mt-1" data-trailer-hero-meta>{{ $featured->getMetaLine() }}</p>
            </div>

            <div class="shrink-0">
                @foreach ($videos as $video)
                    <div class="{{ $loop->first ? '' : 'hidden' }}" data-trailer-hero-action data-code="{{ $video->code }}">
                        <livewire:components.library-button :model="$video->videoable" wire:key="{{ uniqid('hero' . $video->id, true) }}" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
