@props(['theme'])

<div class="max-w-lg sm:max-w-lg md:max-w-xs lg:max-w-sm rounded overflow-hidden shadow-lg">
    <x-picture>
        <img class="w-full aspect-square object-cover object-bottom lazyload" data-sizes="auto" data-src="{{ $theme->screenshot_image_url }}" alt="{{ $theme->name }}" title="{{ $theme->name}}" style="background-color: {{ $theme->screenshot_image?->custom_properties['background_color'] ?? '#FFFFFF' }};">
    </x-picture>

    <div class="px-6 py-4">
        <div class="font-bold text-xl mb-2">{{ $theme->name }}</div>
        <p class="text-gray-700 text-base">{{ trans_choice('{1} :x Download|:x Downloads', $theme->download_count, ['x' => $theme->download_count]) }}</p>
    </div>

    <div class="px-6 pt-4 pb-2">
        <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ __('Premium') }}</span>
    </div>
</div>
