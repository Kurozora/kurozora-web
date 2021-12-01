@props(['anime'])

<div class="relative pb-2">
    <hr class="mb-5" />

    <div class="flex flex-no-wrap">
        <picture class="relative w-64 h-40 mt-2 rounded-lg overflow-hidden sm:w-[35rem] sm:h-[22rem] md:w-[42rem] md:h-[26rem]">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->banner_image_url ?? $anime->poster_image_url ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">

            <div class="absolute bottom-0 left-0 right-0 p-3 pt-[15%] bg-gradient-to-t from-black to-transparent">
                <div class="max-w-[50%]">
                    <p class="text-white leading-tight line-clamp-2">{{ $anime->title }}</p>
                    <p class="text-xs text-white/60 leading-tight line-clamp-2">{{ $anime->tagline ?? $anime->genres?->pluck('name')->join(',  ', ' and ') }}</p>
                </div>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

    <div class="absolute bottom-0 right-0 p-3 pb-5">
        <div class="flex h-10 mt-auto">
            <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
        </div>
    </div>
</div>
