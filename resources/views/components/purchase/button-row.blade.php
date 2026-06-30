@props(['image' => null, 'emoji' => null, 'primary', 'secondary' => null])

<div class="flex items-center gap-4 pt-2 pb-2">
    @if (!empty($emoji))
        <div class="relative">
            <span class="flex items-center justify-center flex-shrink-0 w-16 h-16 text-5xl bg-secondary rounded-xl border border-primary select-none">{{ $emoji }}</span>

            <div class="absolute top-0 left-0 h-full w-full"></div>
        </div>
    @else
        <picture class="relative flex-shrink-0 w-16 h-16">
            <img class="w-full h-full rounded-xl border border-primary object-cover" src="{{ $image }}" alt="{{ $primary }}" width="64" height="64" loading="lazy">

            <div class="absolute top-0 left-0 h-full w-full"></div>
        </picture>
    @endif

    <div class="flex flex-col flex-grow">
        <p class="font-semibold">{{ $primary }}</p>

        @if (!empty($secondary))
            <p class="text-sm text-secondary whitespace-pre-line">{{ $secondary }}</p>
        @endif
    </div>

    {{ $slot }}
</div>
