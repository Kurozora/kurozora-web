@props(['title', 'subtitle', 'color', 'images'])

<div class="relative flex-grow w-64 md:w-80">
    <div class="flex items-end gap-2 pt-4 pb-4">
        <div class="w-full">
            <p class="leading-tight line-clamp-2" title="{{ $title }}">{{ $title }}</p>
            <p class="text-xs leading-tight opacity-75 line-clamp-2" title="{{ $subtitle }}">{{ $subtitle }}</p>
        </div>

        <div class="flex gap-2">
            <livewire:theme-store.get-button :theme-id="strtolower($title)" :name="$title" wire:key="{{ uniqid($title, true) }}" />
        </div>
    </div>

    <div class="flex gap-2 justify-between">
        @foreach($images as $key => $image)
            <picture
                class="relative w-1/3 rounded-lg overflow-hidden"
                style="background-color: {{ $color ?? 'var(--bg-secondary-color)' }};"
                wire:key="{{ uniqid($key, true) }}"
            >
                <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $image }}" alt="{{ $title }} Screenshot {{ $key + 1 }}" title="{{ $title }} Screenshot {{ $key + 1 }}" style="background-color: {{ $color ?? 'var(--bg-secondary-color)' }}; aspect-ratio: 9 / 19.5;">

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
            </picture>
        @endforeach
    </div>
</div>
