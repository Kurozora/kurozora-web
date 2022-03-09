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
                <div wire:loading.delay.shortest>
                    <svg class="animate-spin h-6 w-6 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                </div>
            </div>

            <div class="grid grid-flow-col-dense gap-4 justify-start overflow-x-scroll no-scrollbar">
                @foreach($animeRelations as $relatedAnime)
                    <x-lockups.small-lockup :anime="$relatedAnime->related" :relation="$relatedAnime->relation" />
                @endforeach
            </div>
        </section>
    @endif
</div>
