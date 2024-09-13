@props(['studio', 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col flex-grow w-64 md:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap">
        <picture
            class="relative w-full aspect-video rounded-lg overflow-hidden"
            style="background-color: {{ $studio->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? '#000000' }};"
        >
            <img class="w-full h-full object-cover lazyload"
                 data-sizes="auto"
                 data-src="{{ $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/studio_profile.webp') }}"
                 alt="{{ $studio->name }} Banner"
                 title="{{ $studio->name }}"
                 width="{{ ($studio->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $studio->getFirstMedia(\App\Enums\MediaCollection::Profile))?->custom_properties['width'] ?? 300 }}"
                 height="{{ ($studio->getFirstMedia(\App\Enums\MediaCollection::Banner) ?? $studio->getFirstMedia(\App\Enums\MediaCollection::Profile))?->custom_properties['height'] ?? 300 }}"
            >

            @if (!empty($studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile())))
                <div class="absolute top-0 bottom-0 left-0 right-0 bg-black/20">
                    <div class="flex flex-col flex-wrap h-full text-center items-center justify-center">
                        <picture class="relative h-32 rounded-full shadow-lg overflow-hidden">
                            <img class="w-full h-full object-cover lazyload"
                                 data-sizes="auto"
                                 data-src="{{ $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}"
                                 alt="{{ $studio->name }} Logo" title="{{ $studio->name }}"
                                 width="{{ $studio->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 300 }}"
                                 height="{{ $studio->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 300 }}"
                            >

                            <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                        </picture>
                    </div>
                </div>
            @endif

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('studios.details', $studio) }}"></a>

    <div class="relative flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
                @if ($isRanked)
                    <p class="text-sm font-semibold">#{{ $rank }}</p>
                @endif

                <p class="line-clamp-2">{{ $studio->name }}</p>

                @if (!empty($studio->founded))
                    <p class="text-sm">{{ __('Founded on :x', ['x' => $studio->founded->toFormattedDateString()]) }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
