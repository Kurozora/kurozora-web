@props(['episode'])

<div class="relative">
    <div class="flex flex-no-wrap">
        <picture class="relative rounded-lg overflow-hidden" style="aspect-ratio: 16/9;">
            <img class="lazyload" data-sizes="auto" data-src="{{ $episode->banner_image_url ?? asset('images/static/placeholders/episode_banner.jpg') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">

            <div class="absolute bottom-0 left-0 right-0 p-3 pt-[15%] bg-gradient-to-t from-black to-transparent">
                <div>
                    <p class="text-xs text-white/90 leading-tight line-clamp-2">{{ __('Episode :x', ['x' => $episode->number_total]) }}</p>
                    <p class="text-white leading-tight line-clamp-2">{{ $episode->title }}</p>
                </div>

                <div class="mt-1">
                    <p class="text-xs text-white/90 leading-tight line-clamp-2">{{ $episode->first_aired?->toFormattedDateString() }}</p>
                </div>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('episodes.details', $episode) }}"></a>

    @auth
        <div class="absolute top-0 right-0 left-0 p-3">
            <div class="flex justify-between">
                <livewire:episode.watch-button :episode="$episode" wire:key="{{ md5($episode->id) }}" />
            </div>
        </div>
    @endif
</div>
