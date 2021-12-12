<div class="flex">
    @if ($user->banner_image)
        <picture class="relative w-full overflow-hidden">
            <img class="inline-block w-full h-40 object-cover sm:h-80" style="background-color: {{ $user->banner_image?->custom_properties['background_color'] ?? '#FF9300' }}" src="{{ $user->banner_image_url }}" alt="{{ $user->username }} Banner Image">

            <div class="absolute top-0 left-0 h-full w-full"></div>
        </picture>
    @else
        <div class="inline-block w-full h-40 bg-orange-500 sm:h-80"></div>
    @endif
</div>
