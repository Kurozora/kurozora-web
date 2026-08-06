@props(['image', 'title', 'description'])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2 pb-4 bg-secondary rounded-3xl border border-primary overflow-hidden']) }}>
    <picture class="relative">
        <img class="w-full object-cover" src="{{ $image }}" alt="{{ $title }}" loading="lazy">

        <div class="absolute top-0 left-0 h-full w-full"></div>
    </picture>

    <div class="flex flex-col gap-1 pl-4 pr-4">
        <p class="font-semibold text-center">{{ $title }}</p>
        <p class="text-sm">{{ $description }}</p>
    </div>
</div>
