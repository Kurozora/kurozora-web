@props(['anime'])

<div {{ $attributes->merge(['class' => 'relative flex snap-normal snap-center']) }} style="min-width: 100%;">
    <div class="flex flex-nowrap" style="min-width: 100%;">
        <picture
            class="relative w-full aspect-video overflow-hidden"
            style="background-color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
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

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20"></div>
        </picture>

        <article
            class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3"
            style="padding-top: 15%; background: linear-gradient(transparent, {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-primary-color)' }}); mask-image: linear-gradient(to top, black 50%, transparent); backdrop-filter: blur(8px);"
        >
            <div
                class="max-w-[50%] text-pretty text-balance text-break"
                style="color: {{ $anime->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-primary-color)' }}; filter: invert(1) grayscale(1) brightness(1.3) contrast(9000); mix-blend-mode: luminosity;"
            >
                <p class="text-lg font-semibold leading-tight line-clamp-2 md:text-4xl" title="{{ $anime->title }}">{{ $anime->title }}</p>
                <p class="opacity-75 text-xs leading-tight line-clamp-2 md:text-lg" title="{{ empty($anime->tagline) ? $anime->genres?->pluck('name')->join(', ', ' and ') : $anime->tagline }}">{{ empty($anime->tagline) ? $anime->genres?->pluck('name')->join(', ', ' and ') : $anime->tagline }}</p>
            </div>
        </article>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('anime.details', $anime) }}" wire:navigate></a>

    <div class="absolute bottom-0 right-0 pt-3 pr-3 pl-3 pb-3">
        <div class="flex h-10 mt-auto">
            <livewire:components.library-button :model="$anime" wire:key="{{ uniqid($anime->id, true) }}" />
        </div>
    </div>
</div>
