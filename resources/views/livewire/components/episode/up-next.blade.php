<div wire:init="loadSection">
    @if ($this->nextEpisode->count())
        <section class="pl-4 pr-4">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Up Next') }}
                </x-slot:title>
            </x-section-nav>

            <x-rows.episode-lockup :episodes="$this->nextEpisode" :is-row="false" />
        </section>
    @elseif(!$readyToLoad)
        <section>
            <div class="flex gap-4 justify-between flex-wrap">
                <div class="bg-gray-200 w-64 md:w-80 flex-grow" style="height: 168px;"></div>
            </div>
        </section>
    @endif
</div>
