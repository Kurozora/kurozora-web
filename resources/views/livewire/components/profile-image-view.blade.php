<div class="flex">
    <picture class="relative w-full overflow-hidden">
        <img class="w-24 h-24 bg-white border-2 border-black/5 rounded-full" src="{{ $user->profile_image_url }}" alt="{{ $user->username }} Profile Image" width="{{ $user->profile_image?->custom_properties['width'] ?? 96 }}" height="{{ $user->profile_image?->custom_properties['height'] ?? 96 }}">

        <div class="absolute top-0 left-0 h-full w-full"></div>
    </picture>
</div>
