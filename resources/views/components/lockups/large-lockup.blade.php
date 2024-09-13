@props(['anime'])

<div class="relative w-64 pb-2 snap-normal snap-center md:w-80">
    <div class="flex flex-col">
        <picture
            class="relative aspect-video rounded-lg overflow-hidden"
            style="background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? '#000000' }};"
        >
        @if (empty($anime->video_url))
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        @else
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
        @endif
        </picture>
    </div>

    <div class="relative mt-4">
        <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}" wire:navigate></a>

        <div class="flex gap-2 justify-between">
            <div>
                <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>
                <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ empty($anime->tagline) ? $anime->genres?->pluck('name')->join(', ', ' and ') : $anime->tagline }}</p>
            </div>

            <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
        </div>
    </div>
</div>
