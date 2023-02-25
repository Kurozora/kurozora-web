<div wire:init="loadMangaRelations">
    @if ($mangaRelationsCount)
        <section id="related" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Related Literatures') }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="loadMangaRelations">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ route('games.related-literatures', $game) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <x-rows.small-lockup :related-mangas="$mangaRelations" />
        </section>
    @endif
</div>
