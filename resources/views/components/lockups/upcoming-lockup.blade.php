@props(['anime'])

<div class="relative w-[350px] h-[430px] pb-2">
    <div class="flex flex-no-wrap h-full">
        <picture class="relative w-full rounded-lg overflow-hidden">
            <img class="w-full h-full object-cover" src="{{ $anime->poster_image_url ?? asset('images/static/placeholders/anime_banner.jpg') }}" alt="{{ $anime->title }} Banner" title="{{ $anime->title }}">

            <div class="absolute bottom-0 left-0 right-0 h-[20%] p-3 pt-[15%] bg-gradient-to-t from-black/60 to-transparent"></div>

            <div class="absolute top-0 bottom-0 left-0 right-0 h-full w-full text-center">
                @if (empty($anime->logo_image_url))
                    <p class="relative top-[50%] -translate-y-1/2 px-8 text-3xl text-white font-bold line-clamp-2" style="text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);">{{ $anime->original_title }}</p>
                @else
                    <img class="relative top-[50%] -translate-y-1/2 px-8" src="{{ $anime->logo_image_url }}"
                    alt="{{ $anime->original_title }} Logo">
                @endif
            </div>

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('anime.details', $anime) }}"></a>

    <div class="absolute bottom-0 left-0 right-0 p-3 pb-7">
        <div class="flex flex-col text-center mt-auto">
            <div class="h-10">
                @auth
                    @if(!Auth::user()->isPro())
                        <livewire:anime.reminder-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
                    @else
                        <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
                    @endif
                @else
                    <livewire:anime.library-button :anime="$anime" wire:key="{{ md5($anime->id) }}" />
                @endauth
            </div>

            @if (empty($anime->first_aired))
                <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Coming Soon') }}</p>
            @else
                <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Expected :x', ['x' => $anime->first_aired->toFormattedDateString() ]) }}</p>
            @endif
        </div>
    </div>
</div>