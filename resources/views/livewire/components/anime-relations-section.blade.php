<div wire:init="loadAnimeRelations">
    @if ($animeRelationsCount)
        <section id="related" class="pt-5 pb-8 px-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Related') }}
                </x-slot>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadAnimeRelations">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('anime.related-shows', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.small-lockup :related-animes="$animeRelations" />
        </section>
    @endif
</div>
