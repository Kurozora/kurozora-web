<div wire:init="loadSection">
    @if ($this->episodes->count())
        <section
            id="suggestedEpisodes"
            class="pt-4 pb-8 {{ empty($nextEpisodeID) ? '' : 'border-t border-primary' }}"
        >
            <x-section-nav>
                <x-slot:title>
                    {{ __('See Also') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </x-slot:action>
            </x-section-nav>

            <x-rows.episode-lockup :episodes="$this->episodes" :is-row="false" />
        </section>
    @elseif (!$readyToLoad)
        <section>
            <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>
    @endif
</div>
