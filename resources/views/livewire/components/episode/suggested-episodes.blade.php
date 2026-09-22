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
            <x-skeletons.lockup-row lockup="episode" :is-row="false" :count="9" />
        </section>
    @endif
</div>
