@props(['user'])

<div {{ $attributes->merge(['class' => 'flex aspect-square']) }}>
    <picture
        class="relative bg-white border-2 border-black/5 rounded-full overflow-hidden"
        style="background-color: {{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? '#000000' }};"
    >
        <img src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" width="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}" height="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}">

        <div class="absolute top-0 left-0 h-full w-full"></div>
    </picture>
</div>
