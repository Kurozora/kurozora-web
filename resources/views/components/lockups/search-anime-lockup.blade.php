@props(['anime'])

<div {{ $attributes->merge(['class' => 'relative w-full sm:min-w-[350px]']) }}>
    <div class="flex flex-no-wrap">
        <picture class="relative min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $anime->title }} Poster" title="{{ $anime->title }}">

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

        <div class="flex flex-col gap-2 justify-between w-full">
            <div>
                <div class="flex justify-between">
                    <p class="leading-tight line-clamp-2">{{ $anime->title }}</p>
                    <p class="text-xs leading-tight text-black/60 whitespace-nowrap">{{ $anime->status->name }}</p>
                </div>

                <div class="space-y-1">
                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $anime->tagline ?? $anime->genres?->pluck('name')->join(',  ', ' and ') }}</p>
                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $anime->tv_rating->name }}</p>
                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ trans_choice('{0}|{1} :x Episode|[2,*] :x Episodes', $anime->episode_count, ['x' => $anime->episode_count]) }}</p>
                    <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __($anime->first_aired?->year) }}</p>
                </div>
            </div>

            <div class="flex flex-row flex-no-wrap justify-between space-x-2">
                <div class="inline-flex my-auto">
                    <p class="font-bold text-orange-500">{{ number_format($anime->average_rating, 1) }}</p>

                    <livewire:anime.star-rating :rating="$anime->average_rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid(more_entropy: true) }}" />
                </div>

                <livewire:anime.library-button :anime="$anime" wire:key="{{ uniqid(more_entropy: true) }}" />
            </div>
        </div>
    </div>
</div>