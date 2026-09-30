<section
    id="suggestedEpisodes"
    class="pt-4 pb-8 {{ empty($nextEpisodeID) ? '' : 'border-t border-primary' }}"
    data-section
    data-section-url="{{ $refreshUrl }}"
>
    <x-section-nav>
        <x-slot:title>
            {{ __('See Also') }}
        </x-slot:title>

        <x-slot:action>
            <x-spinner :wire-loading-enabled="false" class="hidden" data-section-spinner />

            @hasrole('superAdmin')
                @if ($refreshUrl)
                    <x-button data-section-refresh="{{ $refreshUrl }}">{{ __('Refresh') }}</x-button>
                @endif
            @endhasrole
        </x-slot:action>
    </x-section-nav>

    <x-rows.episode-lockup :episodes="$episodes" :is-row="false" />
</section>
