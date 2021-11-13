@props(['person'])

<div {{ $attributes->merge(['class' => 'relative w-full pb-2']) }}>
    <div class="flex flex-col">
        <picture class="relative w-28 h-40 rounded-lg shadow-md overflow-hidden md:w-32 md:h-48">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $person->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $person->full_name }} Profile Picture" title="{{ $person->full_name }}">

            <div class="absolute top-0 left-0 h-full w-full border-[1px] border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('people.details', $person) }}"></a>
    </div>

    <div class="mt-2">
        <p class="leading-tight line-clamp-2">{{ $person->full_name }}</p>
    </div>
</div>
