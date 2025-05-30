@props(['anime'])

<div class="relative w-64 pb-2 snap-normal snap-center md:w-80">
    <div class="flex flex-col">
        @if (empty($anime->video_url))
            <picture
                class="relative aspect-video rounded-lg overflow-hidden"
                style="background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        @else
            <div class="relative aspect-video rounded-lg overflow-hidden">
                <iframe
                    class="w-full h-full lazyload"
                    type="text/html"
                    allowfullscreen="allowfullscreen"
                    mozallowfullscreen="mozallowfullscreen"
                    msallowfullscreen="msallowfullscreen"
                    oallowfullscreen="oallowfullscreen"
                    webkitallowfullscreen="webkitallowfullscreen"
                    allow="fullscreen;"
                    data-size="auto"
                    data-src="https://www.youtube-nocookie.com/embed/{{ str($anime->video_url)->after('?v=') }}?autoplay=0&iv_load_policy=3&disablekb=1&color=red&rel=0&cc_load_policy=0&start=0&end=0&origin={{ config('app.url') }}&modestbranding=1&playsinline=1&loop=1&playlist={{ str($anime->video_url)->after('?v=') }}"
                >
                </iframe>
            </div>
        @endif
    </div>

    <div class="relative mt-4">
        <div class="flex flex-nowrap">
            <picture
                class="relative shrink-0 w-28 h-40 mr-2 rounded-lg overflow-hidden"
                style="background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}" wire:navigate></a>

            <div class="flex flex-col gap-2 justify-between">
                <div>
                    <p class="leading-tight line-clamp-2" title="{{ $anime->title }}">{{ $anime->title }}</p>
                    <p class="text-xs leading-tight opacity-75 line-clamp-2" title="{{ $anime->genres?->pluck('name')->join(', ', ' and ') }}">{{ $anime->genres?->pluck('name')->join(', ', ' and ') }}</p>
                </div>

                <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
            </div>
        </div>

        <p class="text-sm leading-tight mt-4">{{ $anime->tagline ?? ' ' }}</p>
    </div>
</div>
