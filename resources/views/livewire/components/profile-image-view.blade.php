<div class="flex">
    <picture class="relative w-full overflow-hidden">
        <img class="w-16 h-16 bg-white border-2 border-black/5 rounded-full sm:w-24 sm:h-24" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" width="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}" height="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}">

        <div class="absolute top-0 left-0 h-full w-full"></div>
    </picture>
</div>
