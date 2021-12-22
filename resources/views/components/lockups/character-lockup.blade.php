@props(['character'])

<div {{ $attributes->merge(['class' => 'relative w-full pb-2']) }}>
    <div class="flex flex-col">
        <picture class="relative w-28 h-40 rounded-lg shadow-md overflow-hidden md:w-32 md:h-48">
            <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $character->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $character->name }} Profile Image" title="{{ $character->name }}">

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </picture>

        <a class="absolute w-full h-full" href="{{ route('characters.details', $character) }}"></a>
    </div>

    <div class="mt-2">
        <p class="leading-tight line-clamp-2">{{ $character->name }}</p>
    </div>
</div>
