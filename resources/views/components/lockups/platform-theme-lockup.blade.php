@props(['theme'])

<div class="relative flex-grow w-64 md:w-80">
    <div class="flex items-end gap-2 pt-4 pb-4">
        <div class="w-full">
            <p class="leading-tight line-clamp-2" title="{{ $theme->name }}">{{ $theme->name }}</p>
            <p class="text-xs leading-tight opacity-75 line-clamp-2" title="{{ \App\Enums\KTheme::OTHER()->descriptionValue($theme) }}">{{ \App\Enums\KTheme::OTHER()->descriptionValue($theme) }}</p>
        </div>

        <div class="flex gap-2">
            <livewire:theme-store.get-button :theme-id="$theme->id" :name="$theme->name" wire:key="{{ uniqid($theme->id, true) }}" />

            @hasrole('superAdmin')
                <x-nova-link :href="route('theme-store.edit', $theme)">
                    @svg('pencil', 'fill-current', ['width' => '44'])
                </x-nova-link>
            @endhasrole
        </div>
    </div>

    <div class="flex gap-2 justify-between">
        @foreach($theme->media as $key => $media)
            <picture
                class="relative w-1/3 rounded-lg overflow-hidden"
                style="background-color: {{ $media?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                wire:key="{{ uniqid($media->id, true) }}"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $media->getFullUrl() }}" alt="{{ $theme->name }} Screenshot {{ $key + 1 }}" title="{{ $theme->name }} Screenshot {{ $key + 1 }}" style="background-color: {{ $media?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }}; aspect-ratio: 9 / 19.5;">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        @endforeach
    </div>
</div>
