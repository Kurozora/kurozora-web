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

    <x-rows.episode-lockup :episodes="$episodes" :is-row="false" />
</section>
