@props(['title', 'description' => null])

<section
    class="flex flex-col items-center justify-center gap-2 mt-4 text-center xl:safe-area-inset"
    style="min-height: 50vh;"
>
    <x-picture>
        <img
            class="w-full max-w-sm"
            src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}"
            title="{{ $title }}"
            alt="{{ $title }}"
        >
    </x-picture>

    <p class="font-bold text-primary">{{ $title }}</p>

    @if($description)
        <p class="text-sm text-secondary max-w-sm">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="pt-4">
            {{ $action }}
        </div>
    @endisset
</section>
