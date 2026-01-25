<div wire:init="loadSection">
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

    @if ($this->episodes->count())
        <section class="mt-4 xl:safe-area-inset">
            <x-rows.episode-lockup :episodes="$this->episodes" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $this->episodes->links() }}
            </div>
        </section>
    @elseif (!$readyToLoad)
        <section id="past-episodes-skeleton" class="xl:safe-area-inset">
            <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                @foreach (range(1,25) as $range)
                    <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                @endforeach
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>
    @endif
</div>
