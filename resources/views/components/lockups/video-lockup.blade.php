@props(['anime'])

<div class="relative w-64 pb-2 md:w-80">
    <div class="flex flex-col">
        @if(empty($anime->video_link))
            <picture class="relative rounded-lg overflow-hidden aspect-ratio-16-9">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->banner_image_url ?? $anime->poster_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">

                <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
            </picture>
        @else
            <video class="mt-2 rounded-lg lazyload"
                   data-sizes="auto"
                   data-src="{{ $anime->video_link }}"
                   poster="{{ $anime->banner_image_url ?? $anime->poster_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}"
                   controls
            >
            </video>
        @endif
    </div>

    <div class="relative mt-4">
        <div class="flex flex-no-wrap">
            <picture class="relative shrink-0 w-28 h-40 mr-2 mr-2 rounded-lg overflow-hidden md:w-32 md:h-48">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

                <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

            <div class="flex flex-col gap-2 justify-between">
                <div>
                    <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>
                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $anime->tagline ?? $anime->genres?->pluck('name')->join(',  ', ' and ') }}</p>
                </div>

                <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
            </div>
        </div>

        <p class="text-sm leading-tight mt-4">{{ $anime->tagline ?? ' ' }}</p>
    </div>
</div>
