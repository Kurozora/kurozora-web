@props(['anime'])

<div class="relative w-[350px]">
    <div class="flex flex-nowrap">
        @if(empty($anime->video_link))
            <picture class="relative mt-2 rounded-lg overflow-hidden">
                <img class="lazyload" data-sizes="auto" data-src="{{ $anime->banner_image_url ?? $anime->poster_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">

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

    <div class="flex flex-col gap-2 justify-between mt-2">
        <div>
            <p class="text-lg leading-tight line-clamp-2">{{ $anime->title }}</p>
            <p class="leading-tight text-black/60 line-clamp-2">{{ $anime->tagline ?? $anime->genres?->pluck('name')->join(',  ', ' and ') }}</p>
        </div>
    </div>
</div>
