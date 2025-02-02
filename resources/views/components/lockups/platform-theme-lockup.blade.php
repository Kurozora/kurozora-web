@props(['theme'])

<div class="relative flex-grow w-64 md:w-80">
    <div class="flex items-end gap-2 pt-4 pr-2 pb-2 pl-2">
        <div class="w-full">
            <p class="leading-tight line-clamp-2">{{ $theme->name }}</p>
            <p class="text-xs leading-tight opacity-75 line-clamp-2">{{ trans_choice('{1} :x Download|:x Downloads', $theme->download_count, ['x' => $theme->download_count]) }}</p>
        </div>

        <div class="flex gap-2">
            <x-button>{{ __('GET') }}</x-button>

            @hasrole('superAdmin')
                <x-nova-link :href="route('theme-store.edit', $theme)">
                    @svg('pencil', 'fill-current', ['width' => '44'])
                </x-nova-link>
            @endhasrole

            {{-- More Options --}}
            <x-dropdown align="right" width="48">
                <x-slot:trigger>
                    <x-circle-button
                        title="{{ __('More') }}"
                    >
                        @svg('ellipsis', 'fill-current', ['width' => '28'])
                    </x-circle-button>
                </x-slot:trigger>

                <x-slot:content>
                    <button
                        class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                    >
                        {{ __('Re-download') }}
                    </button>

                    <button
                        class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                    >
                        {{ __('Delete') }}
                    </button>
                </x-slot:content>
            </x-dropdown>
        </div>
    </div>

    <div class="flex gap-2 justify-between">
        @foreach($theme->media as $key => $media)
{{--            style="background-color: {{ $theme->getFirstMedia(\App\Enums\MediaCollection::Screenshot)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"--}}
            <picture
                class="relative rounded-lg overflow-hidden"
                style="background-color: {{ $media?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                wire:key="wire:key="{{ uniqid($media->id, true) }}"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $media->getFullUrl() }}" alt="{{ $theme->name }} Screenshot {{ $key + 1 }}" title="{{ $theme->name }} Screenshot {{ $key + 1 }}" style="background-color: {{ $media?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }}; aspect-ratio: 9 / 19.5;">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        @endforeach
    </div>
</div>
