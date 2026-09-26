@props(['image' => null, 'icon' => null, 'heading', 'description' => null, 'alt' => null])

<section {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center mt-4 text-center xl:safe-area-inset']) }} style="min-height: 50vh;">
    @if (!empty($icon))
        @svg($icon, 'fill-current w-32')
    @elseif (!empty($image))
        <x-picture>
            <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/' . $image) }}" alt="{{ $alt ?? $heading }}" title="{{ $alt ?? $heading }}">
        </x-picture>
    @endif

    <p class="font-bold">{{ $heading }}</p>

    @if (!empty($description))
        <p class="text-sm text-secondary">{{ $description }}</p>
    @endif

    {{ $slot }}
</section>
