@props(['season'])

<div {{ $attributes->merge(['class' => 'relative w-full sm:max-w-[350px] pb-2']) }}>
    <div class="flex flex-no-wrap">
        <picture class="relative min-w-[100px] max-w-[100px] min-h-[150px] max-h-[150px] mr-2 rounded-lg overflow-hidden">
            <img class="w-full h-full lazyload" data-sizes="auto" data-src="{{ $season->poster_image_url ?? asset('images/static/placeholders/anime_poster.jpg') }}" alt="{{ $season->title }} Poster" title="{{ $season->title }}">

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('seasons.episodes', $season) }}"></a>

        <div class="flex flex-col gap-1 justify-between w-3/4">
            <div>
                <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __('Season') . ' ' . $season->number }}</p>
                <p class="leading-tight line-clamp-2">{{ $season->title }}</p>
            </div>

            <div class="flex flex-wrap gap-1.5 justify-between">
                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm text-black/60">{{ __('Premiere') }}</p>
                    <p class="text-sm">{{ $season->first_aired?->toFormattedDateString() }}</p>
                </div>
                <hr class="w-full" />

                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm text-black/60">{{ __('Episodes') }}</p>
                    <p class="text-sm">{{ $season->episodes()->count() }}</p>
                </div>
                <hr class="w-full" />

                <div class="flex flex-wrap w-full justify-between">
                    <p class="text-sm text-black/60">{{ __('Score') }}</p>
                    <p class="text-sm">0.00</p>
                </div>
            </div>
        </div>
    </div>
</div>
