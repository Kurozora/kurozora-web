<div data-section data-section-refresh-on="refresh-past-episodes" data-section-url="{{ $refreshUrl }}" data-paginated="past-episodes">
    <section class="mb-4 xl:safe-area-inset">
        <div>
            <div class="flex gap-1 pl-4 pr-4">
                <div class="flex flex-wrap items-center w-full">
                    <h1 class="text-2xl font-bold">{{ __('Past Episodes') }}</h1>
                </div>

                <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                </div>
            </div>
        </div>
    </section>

    @if ($episodes->count())
        <section class="mt-4 xl:safe-area-inset">
            <x-rows.episode-lockup :episodes="$episodes" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $episodes->links() }}
            </div>
        </section>
    @endif
</div>
