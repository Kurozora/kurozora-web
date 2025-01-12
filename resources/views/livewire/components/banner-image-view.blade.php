@php
    $bannerImage = $user->getFirstMedia(\App\Enums\MediaCollection::Banner);
@endphp

<div class="flex">
    @if ($bannerImage)
        <picture
            class="relative w-full overflow-hidden"
            style="background-color: {{ $bannerImage->custom_properties['background_color'] ?? 'var(--tint-color)' }}"
        >
            <img
                class="inline-block w-full h-40 object-cover sm:h-80"
                src="{{ $bannerImage->getFullUrl() }}"
                alt="{{ $user->username }} Banner Image"
                width="{{ $bannerImage->custom_properties['width'] ?? 450 }}"
                height="{{ $bannerImage->custom_properties['height'] ?? 160 }}"
            >

            <div class="absolute top-0 left-0 h-full w-full"></div>
        </picture>
    @else
        <div class="inline-block w-full h-40 bg-tint sm:h-80"></div>
    @endif
</div>
