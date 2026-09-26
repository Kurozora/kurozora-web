@props(['platform', 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'lockup-wide' : 'lockup-wide-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <x-lockups.banner-tile
        :name="$platform->name"
        :banner-url="$platform->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $platform->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/platform_profile.webp')"
        :banner-media="$platform->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $platform->getFirstMedia(\App\Enums\MediaCollection::Profile)"
        :profile-url="$platform->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile())"
        :profile-media="$platform->getFirstMedia(\App\Enums\MediaCollection::Profile)"
    />

    <a class="absolute bottom-0 w-full h-full" href="{{ route('platforms.details', $platform) }}" wire:navigate></a>

    <div class="relative flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
                @if ($isRanked)
                    <p class="text-sm leading-tight font-semibold" title="{{ __('Ranked #:x', ['x' => $rank]) }}">{{ __('#:x', ['x' => $rank]) }}</p>
                @endif

                <p class="line-clamp-2" title="{{ $platform->name }}">{{ $platform->name }}</p>

                @if (!empty($platform->started_at))
                    <p class="text-sm opacity-75 line-clamp-2" title="{{ __('Released on :x', ['x' => $platform->started_at->toFormattedDateString()]) }}">{{ __('Released on :x', ['x' => $platform->started_at->toFormattedDateString()]) }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
