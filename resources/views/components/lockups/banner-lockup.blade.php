@props(['anime'])

<div class="relative snap-normal snap-center">
    <div class="flex flex-nowrap">
        <picture
            class="relative w-screen max-w-7xl aspect-video overflow-hidden"
            style="background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? '#000000' }};"
        >
            <img
                class="w-full h-full object-cover lazyload"
                data-sizes="auto"
                data-src="{{ $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $anime->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}"
                alt="{{ $anime->title }} Banner"
                title="{{ $anime->title }}"
                width="{{ ($anime->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $anime->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['width'] ?? 300 }}"
                height="{{ ($anime->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $anime->getFirstMedia(\App\Enums\MediaCollection::Poster))?->custom_properties['height'] ?? 300 }}"
            >

            <div
                class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3 bg-gradient-to-t from-black to-transparent"
                style="padding-top: 15%;"
            >
                <div class="max-w-[50%]">
                    <p class="text-white leading-tight line-clamp-2">{{ $anime->title }}</p>
                    <p class="text-xs text-white/60 leading-tight line-clamp-2">{{ empty($anime->tagline) ? $anime->genres?->pluck('name')->join(', ', ' and ') : $anime->tagline }}</p>
                </div>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('anime.details', $anime) }}" wire:navigate></a>

    <div class="absolute bottom-0 right-0 pt-3 pr-3 pl-3 pb-5">
        <div class="flex h-10 mt-auto">
            <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
        </div>
    </div>
</div>
