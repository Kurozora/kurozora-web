@props(['anime'])

<div class="relative w-[350px]">
    <div class="flex flex-no-wrap">
        <picture class="relative mr-2 rounded-lg overflow-hidden">
            <img class="h-[150px]" src="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.jpg') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

        <div class="flex flex-col gap-2 justify-between w-3/4">
            <div>
                <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>
                <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $anime->tagline ?? $anime->genres->pluck('name')->join(',  ', ' and ') }}</p>
            </div>

            <div class="flex flex-wrap gap-2 justify-between mt-5 h-10">
                <livewire:anime.library-button :anime="$anime" />
            </div>
        </div>
    </div>
</div>
