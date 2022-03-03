@props(['cast', 'isRow' => true])

@php
    $containerWidth = $isRow ? 'sm:w-max' : '';
    $castNamesContainerWidth = $isRow ? 'sm:max-w-[12rem]' : 'w-full';
    /** @var \App\Models\AnimeCast $cast */
@endphp

<div {{ $attributes->merge(['class' => 'relative pb-2 ' . $containerWidth]) }}>
    <div class="flex flex-nowrap">
        <section class="relative flex">
            <picture class="relative shrink-0 w-28 h-40 mr-2 rounded-lg overflow-hidden md:w-32 md:h-48">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $cast->person->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $cast->person->full_name }} Profile Image" title="{{ $cast->person->full_name }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('people.details', $cast->person) }}"></a>
        </section>

        <section class="flex flex-col gap-2 mr-2 {{ $castNamesContainerWidth }}">
            <div class="flex flex-col gap-1">
                <p class="text-orange-500 leading-tight line-clamp-2">{{ $cast->person->full_name }}</p>
                <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __('Voice actor') }}</p>
            </div>

            <div class="flex flex-col text-end gap-1">
                <p class="leading-tight line-clamp-2">{{ __('as :x', ['x' => $cast->character->name]) }}</p>
                <p class="text-xs leading-tight text-black/60 line-clamp-2" >{{ $cast->cast_role->name }}</p>
            </div>
        </section>

        <section class="relative flex">
            <picture class="relative shrink-0 w-28 h-40 rounded-lg overflow-hidden md:w-32 md:h-48">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $cast->character->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $cast->character->name }} Profile Image" title="{{ $cast->character->name }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            <a class="absolute w-full h-full" href="{{ route('characters.details', $cast->character) }}"></a>
        </section>
    </div>
</div>
