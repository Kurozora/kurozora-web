@props(['name', 'bannerUrl', 'bannerMedia' => null, 'profileUrl' => null, 'profileMedia' => null])

<div class="flex flex-nowrap">
    <picture
        class="relative w-full aspect-video rounded-lg overflow-hidden"
        style="background-color: {{ $bannerMedia?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
    >
        <img class="w-full h-full object-cover lazyload"
             data-sizes="auto"
             data-src="{{ $bannerUrl }}"
             alt="{{ $name }} Banner"
             title="{{ $name }}"
             width="{{ $bannerMedia?->custom_properties['width'] ?? 300 }}"
             height="{{ $bannerMedia?->custom_properties['height'] ?? 300 }}"
        >

        @if (!empty($profileUrl))
            <div class="absolute top-0 bottom-0 left-0 right-0 bg-black/20">
                <div class="flex flex-col flex-wrap h-full pt-4 pb-4 text-center items-center justify-center">
                    <picture
                        class="relative h-32 aspect-square rounded-full shadow-lg overflow-hidden"
                        style="background-color: {{ $profileMedia?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                    >
                        <img class="w-full h-full object-cover lazyload"
                             data-sizes="auto"
                             data-src="{{ $profileUrl }}"
                             alt="{{ $name }} Logo" title="{{ $name }}"
                             width="{{ $profileMedia?->custom_properties['width'] ?? 300 }}"
                             height="{{ $profileMedia?->custom_properties['height'] ?? 300 }}"
                        >

                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                    </picture>
                </div>
            </div>
        @endif

        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
    </picture>
</div>
