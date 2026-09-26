@props(['studio', 'rank', 'detail' => null, 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'lockup-banner' : 'lockup-banner-grid';
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    <x-lockups.banner-tile
        :name="$studio->name"
        :banner-url="$studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/studio_profile.webp')"
        :banner-media="$studio->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $studio->getFirstMedia(\App\Enums\MediaCollection::Profile)"
        :profile-url="$studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile())"
        :profile-media="$studio->getFirstMedia(\App\Enums\MediaCollection::Profile)"
    />

    <a class="absolute bottom-0 w-full h-full" href="{{ route('studios.details', $studio) }}" wire:navigate></a>

    <div class="relative flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
                @if ($isRanked)
                    <p class="text-sm leading-tight font-semibold" title="{{ __('Ranked #:x', ['x' => $rank]) }}">{{ __('#:x', ['x' => $rank]) }}</p>
                @endif

                <p class="line-clamp-2" title="{{ $studio->name }}">{{ $studio->name }}</p>

                @if (!empty($detail))
                    <p class="text-sm opacity-75 line-clamp-2">{{ $detail }}</p>
                @elseif (!empty($studio->founded_at))
                    <p class="text-sm opacity-75 line-clamp-2" title="{{ __('Founded on :x', ['x' => $studio->founded_at->toFormattedDateString()]) }}">{{ __('Founded on :x', ['x' => $studio->founded_at->toFormattedDateString()]) }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
