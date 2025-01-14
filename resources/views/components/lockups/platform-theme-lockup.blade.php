@props(['theme'])

<div class="max-w-lg sm:max-w-lg md:max-w-xs lg:max-w-sm bg-secondary rounded overflow-hidden shadow-lg">
    <x-picture
        style="background-color: {{ $theme->getFirstMedia(\App\Enums\MediaCollection::Screenshot)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
    >
        <img class="w-full aspect-square object-cover object-bottom lazyload" data-sizes="auto" data-src="{{ $theme->getFirstMediaFullUrl(\App\Enums\MediaCollection::Screenshot()) }}" alt="{{ $theme->name }}" title="{{ $theme->name }}" style="background-color: {{ $theme->getFirstMedia(\App\Enums\MediaCollection::Screenshot)?->custom_properties['background_color'] ?? '#FFFFFF' }};">
    </x-picture>

    <div class="pt-4 pr-2 pb-4 pl-2">
        <div class="font-bold text-xl mb-2">{{ $theme->name }}</div>
        <p class="text-secondary text-base">{{ trans_choice('{1} :x Download|:x Downloads', $theme->download_count, ['x' => $theme->download_count]) }}</p>
    </div>

    <div class="pt-4 pr-2 pb-2 pl-2">
        <span class="inline-block bg-tertiary rounded-full px-3 pt-1 pb-1 text-sm font-semibold text-secondary mr-2 mb-2">{{ __('Premium') }}</span>
    </div>
</div>
