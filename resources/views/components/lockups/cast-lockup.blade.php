@props(['cast' => null, 'isRow' => true])

@php
    $containerWidth = $isRow ? 'sm:w-max' : '';
    $castNamesContainerWidth = $isRow ? 'sm:max-w-[12rem]' : 'w-full';
    /** @var \App\Models\AnimeCast|\App\Models\GameCast $cast */
@endphp

<div {{ $attributes->merge(['class' => 'relative pb-2 ' . $containerWidth]) }}>
    <div class="flex flex-nowrap">
        <section class="relative flex">
            <picture
                class="relative shrink-0 w-28 h-40 mr-2 rounded-lg overflow-hidden"
                style="background-color: {{ $cast->person?->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $cast->person?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $cast->person?->full_name ?? __('Unknown') }} Profile Image" title="{{ $cast->person?->full_name ?? __('Unknown') }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            @if (!empty($cast->person))
                <a class="absolute w-full h-full" href="{{ route('people.details', $cast->person) }}" wire:navigate></a>
            @endif
        </section>

        <section class="flex flex-col gap-2 mr-2 {{ $castNamesContainerWidth }}">
            <div class="flex flex-col gap-1">
                @if (!empty($cast->person))
                    <a class="text-orange-500 leading-tight line-clamp-2" href="{{ route('people.details', $cast->person) }}">{{ $cast->person->full_name }}</a>
                @else
                    <p class="text-orange-500 leading-tight line-clamp-2">{{ __('Unknown') }}</p>
                @endif
                <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ __('Voice actor') }}</p>
            </div>

            <div class="flex flex-col text-end gap-1">
                <a class="leading-tight line-clamp-2" href="{{ route('characters.details', $cast->character) }}">{{ __('as :x', ['x' => $cast->character->name]) }}</a>
                <p class="text-xs leading-tight opacity-75 line-clamp-2" >{{ $cast->castRole->name }}</p>
            </div>
        </section>

        <section class="relative flex">
            <picture
                class="relative shrink-0 w-28 h-40 rounded-lg overflow-hidden"
                style="background-color: {{ $cast->character->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? '#000000' }};"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $cast->character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $cast->character->name }} Profile Image" title="{{ $cast->character->name }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('characters.details', $cast->character) }}" wire:navigate></a>
        </section>
    </div>
</div>
