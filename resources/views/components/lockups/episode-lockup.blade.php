@props(['episode', 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap">
        <picture class="relative w-full aspect-video rounded-lg overflow-hidden">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $episode->banner_image_url ?? asset('images/static/placeholders/episode_banner.webp') }}" alt="{{ $episode->title }} Banner" title="{{ $episode->title }}">

            <div class="absolute bottom-0 left-0 right-0 p-3 pt-[15%] bg-gradient-to-t from-black/50 to-transparent">
                <div>
                    <p class="text-xs text-white/90 leading-tight line-clamp-2">{{ __('Episode :x', ['x' => $episode->number_total]) }}</p>
                    <p class="text-white leading-tight line-clamp-2">{{ $episode->title }}</p>
                </div>

                <div class="mt-1">
                    <p class="text-xs text-white/90 leading-tight line-clamp-2">{{ $episode->first_aired?->toFormattedDateString() }}</p>
                </div>
            </div>

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('episodes.details', $episode) }}"></a>

    @if (!empty($episode->video_url))
        <div class="absolute top-0 bottom-0 left-0 right-0">
            <div class="flex flex-col justify-center items-center h-full">
                <a class="absolute bottom-0 w-full h-full" href="{{ route('episodes.details', $episode) }}"></a>

                <button
                    class="inline-flex items-center p-5 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                    wire:click="$emit('showVideo', {{ $episode->id }})"
                >
                    @svg('play_fill', 'fill-current', ['width' => '34'])
                </button>
            </div>
        </div>
    @endif

    @auth
        <div class="absolute top-0 right-0 left-0 p-3">
            <div class="flex justify-between">
                <livewire:episode.watch-button :episode="$episode" wire:key="{{ md5($episode->id) }}" />
            </div>
        </div>
    @endif
</div>
