<div wire:init="loadAnimeRelations">
    @if ($animeRelationsCount)
        <section id="related" class="pt-5 pb-8 px-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Related') }}
                </x-slot>

                <x-slot:action>
                    <x-section-nav-link href="{{ route('anime.related-shows', $anime) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot>
            </x-section-nav>

            <div class="flex justify-center">
                <x-spinner />
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($animeRelations as $relatedAnime)
                    <x-lockups.small-lockup :anime="$relatedAnime->related" :relation="$relatedAnime->relation" />
                @endforeach
            </div>
        </section>
    @endif
</div>
