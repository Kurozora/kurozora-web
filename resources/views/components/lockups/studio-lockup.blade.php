@props(['studio'])

@php
/** @var App\Models\Studio $studio */
@endphp

<div class="relative">
    <div class="flex flex-nowrap">
        <picture class="relative w-full aspect-ratio-16-9 rounded-lg overflow-hidden">
            <img class="w-full h-full object-cover lazyload"
                 data-sizes="auto"
                 data-src="{{ $studio->banner_image_url ?? $studio->profile_image_url ?? asset('images/static/placeholders/studio_profile.webp') }}"
                 alt="{{ $studio->name }} Banner"
                 title="{{ $studio->name }}"
                 width="{{ ( $studio->banner_image ?? $studio->profile_image)?->custom_properties['width'] ?? 300}}"
                 height="{{ ($studio->banner_image ?? $studio->profile_image)?->custom_properties['height'] ?? 300 }}"
            >

            @if (!empty($studio->profile_image_url))
                <div class="absolute top-0 bottom-0 left-0 right-0 bg-black/20">
                    <div class="flex flex-col flex-wrap h-full text-center items-center justify-center">
                        <picture class="relative h-32 rounded-full shadow-lg overflow-hidden">
                            <img class="w-full h-full object-cover lazyload"
                                 data-sizes="auto"
                                 data-src="{{ $studio->profile_image_url }}"
                                 alt="{{ $studio->name }} Logo" title="{{ $studio->name }}"
                                 width="{{ $studio->profile_image?->custom_properties['width'] ?? 300 }}"
                                 height="{{ $studio->profile_image?->custom_properties['height'] ?? 300 }}"
                            >

                            <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                        </picture>
                    </div>
                </div>
            @endif

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ route('studios.details', $studio) }}"></a>

    <div>
        <p class="font-semibold">{{ $studio->name }}</p>
        @if (!empty($studio->founded))
            <p class="text-sm">{{ __('Founded on :x', ['x' => $studio->founded->toFormattedDateString()]) }}</p>
        @endif
    </div>
</div>
