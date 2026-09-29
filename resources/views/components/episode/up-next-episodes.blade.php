<div data-section data-section-refresh-on="refresh-up-next-episodes" data-section-url="{{ $refreshUrl }}" data-paginated="episodes">
    @if ($episodes->count())
        <section id="up-next" class="mb-16 xl:safe-area-inset">
            <x-rows.episode-lockup :episodes="$episodes" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $episodes->links() }}
            </div>
        </section>
    @else
        <section id="up-next-empty" class="mb-16 xl:safe-area-inset">
            <div class="flex flex-col items-center justify-center mt-4 text-center">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Up-Next" title="Empty Up-Next">
                </x-picture>

                <p class="font-bold">{{ __('You watched them all!') }}</p>

                <p class="text-sm text-secondary">{{ __('You’re up to date with every episode. Start a new anime to see episodes appear here!') }}</p>
            </div>
        </section>
    @endif
</div>
