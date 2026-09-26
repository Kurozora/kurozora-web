@props(['model', 'href'])

@php
    $backgroundColor = $model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)';
    $gradientColor = $model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-primary-color)';
    $logoUrl = $model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Logo());
@endphp

<div {{ $attributes->merge(['class' => 'relative pb-2 w-[98%] max-w-sm shrink-0 snap-normal snap-start sm:w-80 md:w-[22rem]']) }}>
    <div class="flex flex-nowrap">
        <picture
            class="relative w-full aspect-[3/4] rounded-lg overflow-hidden"
            style="background-color: {{ $backgroundColor }};"
        >
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_banner.webp') }}" alt="{{ $model->title }} Banner" title="{{ $model->title }}" />

            <div
                class="absolute bottom-0 left-0 right-0 pr-3 pb-3 pl-3 bg-gradient-to-t from-black/60 to-transparent"
                style="height: 20%; padding-top: 15%;"
            ></div>

            <div class="absolute top-0 bottom-0 left-0 right-0 h-full w-full text-center">
                @if (empty($logoUrl))
                    <p class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 text-3xl text-white font-bold line-clamp-2" style="text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);">{{ $model->title }}</p>
                @else
                    <img class="relative top-1/2 -translate-y-1/2 pr-8 pl-8 lazyload" data-sizes="auto" data-src="{{ $logoUrl }}" alt="{{ $model->title }} Logo" title="{{ $model->title }}" />
                @endif
            </div>

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>
    </div>

    <a class="absolute bottom-0 w-full h-full" href="{{ $href }}" wire:navigate></a>

    <div class="absolute bottom-2 left-0 right-0 pt-3 pr-3 pl-3 pb-5 rounded-b-lg overflow-hidden" style="padding-top: 15%;">
        <div
            class="absolute top-0 left-0 h-full w-full"
            style="background: linear-gradient(transparent, {{ $gradientColor }}); mask-image: linear-gradient(to top, black 50%, transparent); backdrop-filter: blur(8px);"
        ></div>

        <div class="relative flex flex-col text-center mt-auto">
            <div class="h-10">
                {{ $slot }}
            </div>

            @if (empty($model->started_at))
                <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Coming Soon') }}</p>
            @else
                <p class="mt-2 text-xs text-white font-bold tracking-wide uppercase">{{ __('Expected :x', ['x' => $model->started_at->toFormattedDateString() ]) }}</p>
            @endif
        </div>
    </div>
</div>
