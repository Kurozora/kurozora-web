@props(['staff', 'isRow' => true])

@php
    $containerWidth = $isRow ? 'sm:w-max' : '';
    $staffNamesContainerWidth = $isRow ? 'sm:max-w-[12rem]' : 'w-full';
    /** @var \App\Models\AnimeStaff $staff */
@endphp

<div {{ $attributes->merge(['class' => 'relative pb-2 ' . $containerWidth]) }}>
    <div class="flex flex-nowrap">
        <section class="relative flex">
            <picture class="relative shrink-0 w-28 h-40 mr-2 rounded-lg overflow-hidden md:w-32 md:h-48">
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $staff->person->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $staff->person->full_name }} Profile Image" title="{{ $staff->person->full_name }}">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>

            <div class="flex flex-col gap-1">
                <p class="text-orange-500 leading-tight line-clamp-2">{{ $staff->person->full_name }}</p>
                <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $staff->staff_role->name }}</p>
            </div>

            <a class="absolute w-full h-full" href="{{ route('people.details', $staff->person) }}"></a>
        </section>
    </div>
</div>
