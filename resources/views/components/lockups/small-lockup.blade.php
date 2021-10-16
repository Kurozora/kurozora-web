@props(['anime', 'relation'])

<div {{ $attributes->merge(['class' => 'relative w-full sm:min-w-[350px] sm:max-w-[350px] pb-2']) }}>
    <div class="flex flex-no-wrap">
        <picture class="relative min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.jpg') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

        <div class="flex flex-col gap-2 justify-between w-3/4">
            <div>
                @if (!empty($relation))
                    <p class="text-xs leading-tight font-semibold text-black/60 line-clamp-2">{{ $relation->name }}</p>
                @endif
                <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>
                <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $anime->tagline ?? $anime->genres?->pluck('name')->join(',  ', ' and ') }}</p>
            </div>

            <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
        </div>
    </div>
</div>
